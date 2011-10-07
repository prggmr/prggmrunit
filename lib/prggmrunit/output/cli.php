<?php
namespace prggmrunit\output;
/**
 *  Copyright 2010-11 Nickolas Whiting
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 *
 * @author  Nickolas Whiting  <prggmr@gmail.com>
 * @package  prggmrunit
 * @copyright  Copyright (c), 2010-11 Nickolas Whiting
 */

use prggmrunit as unit;

/**
 * Generates output for the command line.
 */
class CLI extends unit\Output {
    
    /**
     * Output using colors.
     *
     * @var  boolean
     */
    static protected $_colors = false;
    
    /**
     * Lets keep track of some things.
     *
     * @var  int
     */
    static public $start_time = null;
    static public $end_time = null;
    static public $echo_c = 0;
    
    /**
     * Compiles the CLI output generator.
     *
     * Setup colors, our startup events, assertion printouts and end action.
     *
     * @return  void
     */
    public function __construct(/* ... */)
    {
        // check for colors
        if (defined('PRGGMRUNIT_OUTPUT_COLORS')) {
            if (PRGGMRUNIT_OUTPUT_COLORS === true) {
                static::$_colors = true;
            }
        }
        
        // Testing beings
        static::$_prggmrunit->subscribe(\prggmrunit\Events::START, function(){
            CLI::$start_time = \Prggmrunit::instance()->getMilliseconds();
            CLI::send(sprintf(
                "prggmrunit %s %s%s%s",
                PRGGMRUNIT_VERSION, PRGGMRUNIT_MASTERMIND, PHP_EOL, PHP_EOL
            ));
        });
        
        // Assertion Pass
        static::$_prggmrunit->subscribe(\prggmrunit\Events::TEST_ASSERTION_PASS, function(){
            CLI::send(".");
        });
        
        // Assertion Fail
        static::$_prggmrunit->subscribe(\prggmrunit\Events::TEST_ASSERTION_FAIL, function(){
            CLI::send("F", CLI::ERROR);
        });
        
        // Assertion Skip
        static::$_prggmrunit->subscribe(\prggmrunit\Events::TEST_ASSERTION_SKIP, function(){
            CLI::send("S", CLI::DEBUG);
        });
        
        // Testing is finished
        static::$_prggmrunit->subscribe(\prggmrunit\Events::END, function($test){
    
            $end_time = \Prggmrunit::instance()->getMilliseconds();
            $tests = \Prggmrunit::instance()->getTests();
            
            // testing totals
            $testsP = 0;
            $assertionP = 0;
            $testsF = 0;
            $assertionF = 0;
            $testsS = 0;
            $assertionS = 0;
            $testsC = 0;
            $assertionC = 0;
            
            $failures = array();
            
            foreach ($tests as $_index => $_test) {
                $testsC++;
                switch ($_test->getState()) {
                    case \prggmrunit\Test::FAIL:
                        $failures[$_index] = $_test->getStateMessage();
                        $testsF += 1;
                        break;
                    case \prggmrunit\Test::PASS:
                        $testsP += 1;
                        break;
                    case \prggmrunit\Test::SKIP:
                        $testsS += 1;
                        break;
                }
                $assertionF = $assertionF + $_test->failedAssertions();
                $assertionP = $assertionP + $_test->passedAssertions();
                $assertionS += $_test->skippedAssertions();
                $assertionC += $_test->assertionCount();
            }
            
            $runtime = round(($end_time - CLI::$start_time) / 1000, 4);
            if (0 != count($failures)) {
                
                CLI::send(sprintf(
                    "%s%s====================================================",
                    PHP_EOL, PHP_EOL
                ), CLI::ERROR);
                CLI::send(sprintf(
                    "%sFailures Detected",
                    PHP_EOL
                ), CLI::ERROR);
                foreach ($failures as $_k => $_fail) {
                    CLI::send(sprintf(
                        "%s------------------------------------------------%s",
                        PHP_EOL, PHP_EOL
                    ), CLI::ERROR);
                    $file = function($file) {
                        $array = explode('/', $file);
                        return str_replace('.php', '', $array[count($array)-1]);
                    };
                    CLI::send(sprintf(
                        "FAIL : %s (%s)%s------------------------------------",
                        $_fail[1][0]['file'],
                        $_k,
                        PHP_EOL
                    ), CLI::ERROR);
                    CLI::send(sprintf(
                        "%sLine : %s%sMessage : %s%s%s",
                        PHP_EOL,
                        $_fail[1][0]['line'],
                        PHP_EOL,
                        $_fail[0],
                        PHP_EOL, PHP_EOL
                    ), CLI::ERROR);
                }
            }
            
            $size = function($size) {
                /**
                 * This was authored by another individual
                 */
                $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
                return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
            };
            
            CLI::send(sprintf(
                "%s===================================================%s",
                PHP_EOL, PHP_EOL
            ), CLI::DEBUG);
            CLI::send(sprintf(
                "Ran %s tests in %s seconds and used %s%s%s",
                $testsC,
                $runtime,
                $size(round(memory_get_peak_usage(true), 4)),
                PHP_EOL, PHP_EOL
            ), CLI::DEBUG);
            if ($testsF != 0) {
                CLI::send(sprintf(
                    "FAIL (failures=%s, success=%s, skipped=%s)",
                    $testsF, $testsP, $testsS
                ), CLI::ERROR);
            } else {
                CLI::send(sprintf(
                    "PASS (success=%s, skipped=%s)",
                    $testsP,
                    $testsS
                ));
            }
            CLI::send(sprintf(
                "%sAssertions (pass=%s, fail=%s, skip=%s)%s",
                PHP_EOL, $assertionP, $assertionF, $assertionS, PHP_EOL
            ));
        });
    }
    
    /**
     * Sends a string to output.
     *
     * @param  string  $string
     * @param  string  $type  
     *
     * @return  void
     */
    public static function send($string, $type = null)
    {
        $message = null;
        switch ($type) {
            default:
            case unit\Output::MESSAGE:
                if (static::$_colors) {
                    $message .= "\033[1;32m";
                }
                $message .= sprintf("%s",
                    $string
                );
                if (static::$_colors) {
                    $message .= "\033[0m";
                }
                break;
            case unit\Output::ERROR:
                if (static::$_colors) {
                    $message .= "\033[1;31m";
                }
                $message .= sprintf("%s",
                    $string
                );
                if (static::$_colors) {
                    $message .= "\033[0m";
                }
                break;
            case unit\Output::DEBUG:
                if (static::$_colors) {
                    $message .= "\033[1;35m";
                }
                $message .= sprintf("%s",
                    $string
                );
                if (static::$_colors) {
                    $message .= "\033[0m";
                }
                break;
            case unit\Output::SYSTEM:
                if (static::$_colors) {
                    $message .= "\033[1;34m";
                }
                $message .= sprintf("%s",
                    $string
                );
                if (static::$_colors) {
                    $message .= "\033[0m";
                }
                break;
        }
        print($message);
    }
}