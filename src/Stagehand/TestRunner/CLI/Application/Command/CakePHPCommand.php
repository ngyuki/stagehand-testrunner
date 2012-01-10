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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Stagehand\TestRunner\Core\Configuration\CakePHPConfiguration;
use Stagehand\TestRunner\Core\Plugin\CakePHPPlugin;
use Stagehand\TestRunner\Core\Plugin\PluginFinder;
use Stagehand\TestRunner\Core\Transformation\Transformation;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class CakePHPCommand extends SimpleTestCommand
{
    protected function getPlugin()
    {
        return PluginFinder::findByPluginID(CakePHPPlugin::getPluginID());
    }

    protected function doConfigure()
    {
        parent::doConfigure();

        if ($this->getPlugin()->hasFeature('cakephp_app_path')) {
            $this->addOption('cakephp-app-path', null, InputOption::VALUE_REQUIRED, 'The path of your app folder. <comment>(default: The working directory at testrunner startup)</comment>');
        }

        if ($this->getPlugin()->hasFeature('cakephp_core_path')) {
            $this->addOption('cakephp-core-path', null, InputOption::VALUE_REQUIRED, 'The path of your CakePHP libraries folder (/path/to/cake). <comment>(default: The "cake" directory under the parent directory of your app folder is used. (/path/to/app/../cake))</comment>');
        }
    }

    protected function doTransformToConfiguration(InputInterface $input, OutputInterface $output, Transformation $transformation)
    {
        parent::doTransformToConfiguration($input, $output, $transformation);

        if ($this->getPlugin()->hasFeature('cakephp_app_path')) {
            if (!is_null($input->getOption('cakephp-app-path'))) {
                $transformation->setConfigurationPart(
                    CakePHPConfiguration::getConfigurationID(),
                    array('app_path' => $input->getOption('cakephp-app-path'))
                );
            }
        }

        if ($this->getPlugin()->hasFeature('cakephp_core_path')) {
            if (!is_null($input->getOption('cakephp-core-path'))) {
                $transformation->setConfigurationPart(
                    CakePHPConfiguration::getConfigurationID(),
                    array('core_path' => $input->getOption('cakephp-core-path'))
                );
            }
        }
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
