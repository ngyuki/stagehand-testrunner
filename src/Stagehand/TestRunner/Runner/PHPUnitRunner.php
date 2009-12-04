<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.1.0
 */

require_once 'PHPUnit/TextUI/TestRunner.php';

// {{{ Stagehand_TestRunner_Runner_PHPUnitRunner

/**
 * A test runner for PHPUnit.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class Stagehand_TestRunner_Runner_PHPUnitRunner extends Stagehand_TestRunner_Runner
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ run()

    /**
     * Runs tests based on the given PHPUnit_Framework_TestSuite object.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function run($suite)
    {
        if ($this->config->logsJUnitXMLToStdout) {
            $junitXMLPrinter = new Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_JUnitXMLPrinter();
            $junitXMLPrinter->setXMLWriter(
                new Stagehand_TestRunner_Runner_JUnitXMLWriter_JUnitXMLStreamWriter(
                    array($junitXMLPrinter, 'write')
                )
            );
            PHPUnit_TextUI_TestRunner::run($suite, array('printer' => $junitXMLPrinter));
            return;
        }

        $testResult = new PHPUnit_Framework_TestResult();
        $printer = new Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_ResultPrinter(
                       null, false, $this->config->colors
                   );

        $arguments = array();
        $arguments['printer'] = $printer;

        Stagehand_TestRunner_Runner_PHPUnitRunner_TestDox_Stream::register();
        $arguments['listeners'] =
            array(
                new Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_TestDoxPrinter(
                    'testdox://' . spl_object_hash($testResult),
                    $this->config->colors,
                    $this->prettifier()
                )
            );
        if (!$this->config->printsDetailedProgressReport) {
            $arguments['listeners'][] =
                new Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_ProgressPrinter(
                    null, false, $this->config->colors
                                                                                      );
        } else {
            $arguments['listeners'][] =
                new Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_DetailedProgressPrinter(
                    null, false, $this->config->colors
                                                                                              );
        }

        if (!is_null($this->config->junitLogFile)) {
            $junitXMLListener = new Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_JUnitXMLPrinter($this->config->junitLogFile);
            $junitXMLListener->setXMLWriter(
                new Stagehand_TestRunner_JUnitXMLWriter_JUnitXMLDOMWriter(
                    array($junitXMLListener, 'write')
                )
            );
            $arguments['listeners'][] = $junitXMLListener;
        }

        $testRunner = new Stagehand_TestRunner_Runner_PHPUnitRunner_TestRunner();
        $testRunner->setTestResult($testResult);
        $testRunner->doRun($suite, $arguments);

        if ($this->config->usesGrowl) {
            ob_start();
            $printer->printResult($testResult);
            $output = ob_get_contents();
            ob_end_clean();

            if (preg_match('/^(?:\x1b\[3[23]m)?(OK[^\x1b]+)/ms', $output, $matches)) {
                $this->notification->name = 'Green';
                $this->notification->description = $matches[1];
            } elseif (preg_match('/^(FAILURES!\s)(?:\x1b\[31m)?([^\x1b]+)/ms', $output, $matches)) {
                $this->notification->name = 'Red';
                $this->notification->description = "{$matches[1]}{$matches[2]}";
            }
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ prettifier()

    /**
     * @return Stagehand_TestRunner_Runner_PHPUnitRunner_TestDox_NamePrettifier
     * @since Method available since Release 2.7.0
     */
    protected function prettifier()
    {
        return new Stagehand_TestRunner_Runner_PHPUnitRunner_TestDox_NamePrettifier();
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

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
