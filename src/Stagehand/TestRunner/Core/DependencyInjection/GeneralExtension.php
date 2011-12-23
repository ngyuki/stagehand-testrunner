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

namespace Stagehand\TestRunner\Core\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Stagehand\TestRunner\Core\Configuration\GeneralConfiguration;
use Stagehand\TestRunner\Core\Package;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class GeneralExtension extends Extension
{
    public function getAlias()
    {
        return 'general';
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function transformConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter(Package::PACKAGE_ID . '.' . 'plugin_id', $config['testing_framework']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'recursively_scans', $config['recursively_scans']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'colors', $config['colors']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'preload_file', $config['preload_file']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'enables_autotest', $config['enables_autotest']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'monitoring_directories', $config['monitoring_directories']);

        if (array_key_exists('uses_notification', $config)) {
            $container->setParameter(Package::PACKAGE_ID . '.' . 'uses_notification', $config['uses_notification']);
        }

        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_methods', $config['test_methods']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_classes', $config['test_classes']);

        if (array_key_exists('junit_xml', $config)) {
            $container->setParameter(Package::PACKAGE_ID . '.' . 'junit_xml_file', $config['junit_xml']['file']);
            $container->setParameter(Package::PACKAGE_ID . '.' . 'logs_results_in_junit_xml_in_realtime', $config['junit_xml']['realtime']);
        }

        $container->setParameter(Package::PACKAGE_ID . '.' . 'stops_on_failure', $config['stops_on_failure']);

        if (array_key_exists('test_file_pattern', $config)) {
            $container->setParameter(Package::PACKAGE_ID . '.' . 'test_file_pattern', $config['test_file_pattern']);
        }

        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_resources', $config['test_resources']);
    }

    protected function createConfiguration()
    {
        return new GeneralConfiguration();
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
