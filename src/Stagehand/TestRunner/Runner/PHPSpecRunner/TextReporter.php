<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2007-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpspec.org/
 * @since      File available since Release 2.0.0
 */

namespace Stagehand\TestRunner\Runner\PHPSpecRunner;

use Stagehand\TestRunner\Util\Coloring;

/**
 * A reporter for PHPSpec.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpspec.org/
 * @since      Class available since Release 2.0.0
 */
class TextReporter extends \PHPSpec_Runner_Reporter_Text
{
    protected $colors;

    /**
     * @param \PHPSpec_Runner_Result $result
     * @param boolean $colors
     */
    public function __construct(\PHPSpec_Runner_Result $result, $colors)
    {
        parent::__construct($result);
        $this->colors = $colors;
    }

    /**
     * @param string $symbol
     */
    public function outputStatus($symbol)
    {
        if ($this->colors) {
            switch ($symbol) {
            case '.':
                $symbol = Coloring::green($symbol);
                break;
            case 'F':
                $symbol = Coloring::red($symbol);
                break;
            case 'E':
                $symbol = Coloring::magenta($symbol);
                break;
            case 'P':
                $symbol = Coloring::yellow($symbol);
                break;
            }
        }

        parent::outputStatus($symbol);
    }

    /**
     * @param boolean $specs
     */
    public function output($specs = false)
    {
        $output = preg_replace(array('/(\x0d|\x0a|\x0d\x0a){3,}/', '/^(  -)(.+)/m'),
                               array("\n\n", '$1 $2'),
                               $this->toString($specs)
                               );

        if ($this->colors) {
            $failuresCount = $this->_result->countFailures();
            $deliberateFailuresCount = $this->_result->countDeliberateFailures();
            $errorsCount = $this->_result->countErrors();
            $exceptionsCount = $this->_result->countExceptions();
            $pendingsCount = $this->_result->countPending();

            if ($failuresCount + $deliberateFailuresCount + $errorsCount + $exceptionsCount + $pendingsCount == 0) {
                $colorLabel = 'green';
            } elseif ($pendingsCount && $failuresCount + $deliberateFailuresCount + $errorsCount + $exceptionsCount == 0) {
                $colorLabel = 'yellow';
            } else {
                $colorLabel = 'red';
            }

            $output = preg_replace(
                array(
                    '/^(\d+ examples?.*)/m',
                    '/^(  -)(.+)( \(ERROR|EXCEPTION\))/m',
                    '/^(  -)(.+)( \(FAIL\))/m',
                    '/^(  -)(.+)( \(DELIBERATEFAIL\))/m',
                    '/^(  -)(.+)( \(PENDING\))/m',
                    '/^(  -)(.+)/m',
                    '/(\d+\)\s+)(.+ (?:ERROR|EXCEPTION)\s+.+)/',
                    '/(\d+\)\s+)(.+ FAILED\s+.+)/',
                    '/(\d+\)\s+)(.+ PENDING\s+.+)/',
                    '/^((?:Errors|Exceptions):)/m',
                    '/^(Failures:)/m',
                    '/^(Pending:)/m'
                ),
                array(
                    Coloring::$colorLabel('$1'),
                    Coloring::magenta('$1$2$3'),
                    Coloring::red('$1$2$3'),
                    Coloring::red('$1$2$3'),
                    Coloring::yellow('$1$2$3'),
                    Coloring::green('$1$2$3'),
                    '$1' . Coloring::magenta('$2'),
                    '$1' . Coloring::red('$2'),
                    '$1' . Coloring::yellow('$2'),
                    Coloring::magenta('$1'),
                    Coloring::red('$1'),
                    Coloring::yellow('$1')
                ),
                $output
            );
        }

        echo $output;
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
