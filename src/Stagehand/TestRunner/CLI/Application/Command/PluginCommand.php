<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011 KUBO Atsuhiro <kubo@iteman.jp>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\CLI\Application\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\ConfigurationTransformer;
use Stagehand\TestRunner\Core\Configuration\GeneralConfiguration;
use Stagehand\TestRunner\Util\FileSystem;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
abstract class PluginCommand extends Command
{
    /**
     * @var \Stagehand\TestRunner\Util\FileSystem
     */
    protected $fileSystem;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->fileSystem = new FileSystem();
    }

    protected function configure()
    {
        parent::configure();

        $this->setName(strtolower($this->getPlugin()->getPluginID()));
        $this->setDescription('Runs tests with ' . $this->getPlugin()->getPluginID() . '.');
        $this->setHelp(
'The <info>' . $this->getName() . '</info> command runs tests with ' . $this->getPlugin()->getPluginID() . ':' . PHP_EOL .
PHP_EOL .
'  <info>testrunner ' . $this->getName() . ' ...</info>'
        );

        $this->addArgument('test_directory_or_file', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'The directory or file that contains tests to be run <comment>(default: The working directory at testrunner startup)</comment>');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'The YAML-based configuration file for Stagehand_TestRunner');
        $this->addOption('recursive', 'R', InputOption::VALUE_NONE, 'Recursively runs tests in the specified directories.');

        if ($this->getPlugin()->hasFeature('autotest')) {
            $this->addOption('autotest', 'a', InputOption::VALUE_NONE, 'Monitors for changes in the specified directories and run tests when changes are detected.');
            $this->addOption('watch-dir', 'w', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'The directory to be monitored for changes <comment>(default: The directories specified by the arguments)</comment>');
        }

        if ($this->getPlugin()->hasFeature('notify')) {
            $this->addOption('notify', 'n', InputOption::VALUE_NONE, 'Notifies test results by using the growlnotify command in Mac OS X and Windows or the notify-send command in Linux.');
        }

        if ($this->getPlugin()->hasFeature('stop_on_failure')) {
            $this->addOption('stop-on-failure', 's', InputOption::VALUE_NONE, 'Stops the test run when the first failure or error is raised.');
        }

        if ($this->getPlugin()->hasFeature('junit_xml')) {
            $this->addOption('log-junit', null, InputOption::VALUE_REQUIRED, 'Logs test results into the specified file in the JUnit XML format.');
            $this->addOption('log-junit-realtime', null, InputOption::VALUE_NONE, 'Logs test results in real-time into the specified file in the JUnit XML format.');
        }

        $this->addOption('test-file-pattern', null, InputOption::VALUE_REQUIRED, 'The regular expression pattern for test files <comment>(default: ' . $this->getPlugin()->getTestFilePattern() . ')</comment');

        if ($this->getPlugin()->hasFeature('test_methods')) {
            $this->addOption('test-method', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'The test method to be run');
        }

        if ($this->getPlugin()->hasFeature('test_classes')) {
            $this->addOption('test-class', null, InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'The test class to be run');
        }

        $this->doConfigure();
    }

    abstract protected function doConfigure();

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->createContainer();
        ApplicationContext::getInstance()->getComponentFactory()->setContainer($container);
        ApplicationContext::getInstance()->setPlugin($this->getPlugin());
        ApplicationContext::getInstance()->setComponent('input', $input);
        ApplicationContext::getInstance()->setComponent('output', $output);
        $configurationTransformer = $this->createConfigurationTransformer($container);
        $this->transformToConfiguration($input, $output, $configurationTransformer);
        $configurationTransformer->transformToContainer();
        $this->createTestRunner()->run();
        return 0;
    }

    /**
     * @return \Stagehand\TestRunner\Core\Plugin\Plugin
     */
    abstract protected function getPlugin();

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Stagehand\TestRunner\Core\ConfigurationTransformer $configurationTransformer
     */
    protected function transformToConfiguration(InputInterface $input, OutputInterface $output, ConfigurationTransformer $configurationTransformer)
    {
        if (!is_null($input->getOption('config'))) {
            $configurationTransformer->setConfigurationFile(
                 $this->fileSystem->getAbsolutePath(
                     $input->getOption('config'),
                     ApplicationContext::getInstance()->getEnvironment()->getWorkingDirectoryAtStartup()
                 )
            );
        }

        if (count($input->getArgument('test_directory_or_file')) > 0) {
            $configurationTransformer->setConfigurationPart(
                GeneralConfiguration::getConfigurationID(),
                array('test_targets' => array('resources' => $input->getArgument('test_directory_or_file')))
            );
        }
        if ($input->getOption('recursive')) {
            $configurationTransformer->setConfigurationPart(
                GeneralConfiguration::getConfigurationID(),
                array('test_targets' => array('recursive' => true))
            );
        }
        if ($this->getPlugin()->hasFeature('test_methods')) {
            if (count($input->getOption('test-method')) > 0) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('test_targets' => array('methods' => $input->getOption('test-method')))
                );
            }
        }
        if ($this->getPlugin()->hasFeature('test_classes')) {
            if (count($input->getOption('test-class')) > 0) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('test_targets' => array('classes' => $input->getOption('test-class')))
                );
            }
        }
        if (!is_null($input->getOption('test-file-pattern'))) {
            $configurationTransformer->setConfigurationPart(
                GeneralConfiguration::getConfigurationID(),
                array('test_targets' => array('file_pattern' => $input->getOption('test-file-pattern')))
            );
        }

        if ($this->getPlugin()->hasFeature('autotest')) {
            if ($input->getOption('autotest')) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('autotest' => array('enabled' => true))
                );
            }
            if (count($input->getOption('watch-dir')) > 0) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('autotest' => array('watch_dirs' => $input->getOption('watch-dir')))
                );
            }
        }

        if ($this->getPlugin()->hasFeature('notify')) {
            if ($input->getOption('notify')) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('notify' => true)
                );
            }
        }

        if ($this->getPlugin()->hasFeature('junit_xml')) {
            if (!is_null($input->getOption('log-junit'))) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('junit_xml' => array('file' => $input->getOption('log-junit')))
                );
            }
            if ($input->getOption('log-junit-realtime')) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('junit_xml' => array('realtime' => true))
                );
            }
        }

        if ($this->getPlugin()->hasFeature('stop_on_failure')) {
            if ($input->getOption('stop-on-failure')) {
                $configurationTransformer->setConfigurationPart(
                    GeneralConfiguration::getConfigurationID(),
                    array('stop_on_failure' => true)
                );
            }
        }

        $this->doTransformToConfiguration($input, $output, $configurationTransformer);
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Stagehand\TestRunner\Core\ConfigurationTransformer $configurationTransformer
     */
    abstract protected function doTransformToConfiguration(InputInterface $input, OutputInterface $output, ConfigurationTransformer $configurationTransformer);

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function createContainer()
    {
        return new ContainerBuilder();
    }

    /**
     * @return \Stagehand\TestRunner\CLI\TestRunner
     */
    protected function createTestRunner()
    {
        return ApplicationContext::getInstance()->createComponent('test_runner');
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    protected function createConfigurationTransformer(ContainerBuilder $container)
    {
        return new ConfigurationTransformer($container);
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */