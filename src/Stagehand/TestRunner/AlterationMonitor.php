<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://winbinder.org/
 * @since      File available since Release 2.1.0
 */

require_once 'Stagehand/TestRunner/Exception.php';

// {{{ Stagehand_TestRunner_AlterationMonitor

/**
 * The file and directory alteration monitor.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://winbinder.org/
 * @since      Class available since Release 2.1.0
 */
class Stagehand_TestRunner_AlterationMonitor
{

    // {{{ constants

    const SCAN_INTERVAL_MIN = 5;

    // }}}
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

    private $_directory;
    private $_command;
    private $_currentElements = array();
    private $_previousElements = array();
    private $_isFirstTime = true;
    private $_excludePatterns = array('!^CVS$!',
                                      '!^.svn!',
                                      '!\.swp$!',
                                      '!~$!',
                                      '!\.bak$!',
                                      '!^#.+#$!'
                                      );
    private $_scanInterval = self::SCAN_INTERVAL_MIN;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Sets a directory and command string to the properties.
     *
     * @param string $directory
     * @param string $command
     */
    public function __construct($directory, $command)
    {
        $this->_directory = $directory;
        $this->_command = $command;
    }

    // }}}
    // {{{ monitor()

    /**
     * Watches for changes in the directory and runs tests in the directory
     * recursively when changes are detected.
     */
    public function monitor()
    {
        $this->_runTests();

        while (true) {
            print "
Waiting for changes in the directory [ {$this->_directory} ] ...
";

            $this->_waitForChanges();

            print "Any changes are detected!
Running tests by the command [ {$this->_command} ] ...

";
            $this->_runTests();
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _runTests()

    /**
     * Runs tests in the directory recursively.
     */
    private function _runTests()
    {
        passthru($this->_command, $result);
    }

    // }}}
    // {{{ _waitForChanges()

    /**
     * Watches for changes in the directory and returns immediately when
     * changes are detected.
     */
    private function _waitForChanges()
    {
        $this->_previousElements = array();
        $this->_isFirstTime = true;

        while (true) {
            sleep($this->_scanInterval);
            clearstatcache();

            try {
                $this->_currentElements = array();
                $startTime = time();
                $this->_collectElements($this->_directory);
                $endTime = time();
                $elapsedTime = $endTime - $startTime;
                if ($elapsedTime > self::SCAN_INTERVAL_MIN) {
                    $this->_scanInterval = $elapsedTime;
                }
            } catch (Stagehand_TestRunner_Exception $e) {
                return;
            }

            if (!$this->_isFirstTime) {
                reset($this->_previousElements);
                while (list($key, $value) = each($this->_previousElements)) {
                    if (!array_key_exists($key, $this->_currentElements)) {
                        return;
                    }
                }
            }

            $this->_previousElements = $this->_currentElements;
            $this->_isFirstTime = false;
        }
    }

    // }}}
    // {{{ _collectElements()

    /**
     * Collects all files and directories in the directory and detects any
     * changes of a file or directory immediately.
     *
     * @param string $directory
     * @throws Stagehand_TestRunner_Exception
     */
    private function _collectElements($directory)
    {
        $files = scandir($directory);
        for ($i = 0, $count = count($files); $i < $count; ++$i) {
            if ($files[$i] == '.' || $files[$i] == '..') {
                continue;
            }

            foreach ($this->_excludePatterns as $excludePattern) {
                if (preg_match($excludePattern, $files[$i])) {
                    continue 2;
                }
            }

            $element = $directory . DIRECTORY_SEPARATOR . $files[$i];
            if (!$this->_isFirstTime) {
                if (!array_key_exists($element, $this->_previousElements)) {
                    throw new Stagehand_TestRunner_Exception();
                }

                if (!is_dir($element)) {
                    $mtime = filemtime($element);
                    if ($this->_previousElements[$element]['mtime'] != $mtime) {
                        throw new Stagehand_TestRunner_Exception();
                    }
                }

                $perms = fileperms($element);
                if ($this->_previousElements[$element]['perms'] != $perms) {
                    throw new Stagehand_TestRunner_Exception();
                }
            }

            if (!is_dir($element)) {
                $this->_currentElements[$element]['mtime'] = filemtime($element);
            }

            $this->_currentElements[$element]['perms'] = fileperms($element);

            if (is_dir($element)) {
                $this->_collectElements($element);
            }
        }
    }

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