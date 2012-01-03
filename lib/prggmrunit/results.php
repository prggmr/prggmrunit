<?php
namespace prggmrunit;
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
 
 /**
  * Contains the results of a finished prggmrunit tests.
  */
class Results {
    
    /**
     * Results array.
     *
     * @var  array 
     */
    protected $_results = array();
    
    /**
     * Array of messages returned from testing.
     * 
     * @var  array
     */
    protected $_messages = array();
    
    /**
     * Array of final tests.
     *
     * @var  array
     */
    protected $_tests = array();
    
    /**
     * Avaliable keys in the result array.
     *
     * @var array
     */
    public $avaliableResults = array(
        'getPassedTests',
        'getFailedTests',
        'getSkippedTests',
        'getTestsTotal',
        'getSuitesTotal',
        'getFailedAssertions',
        'getPassedAssertions',
        'getSkippedAssertions',
        'getTotalAssertions',
        'getRuntime',
        'getMemoryUsage'
    );

     
    /**
     * Constructs a new results object.
     * 
     * @param  object  $engine  \prggmrunit\Engine 
     *
     * @return  void 
     */
     public function __construct(\prggmrunit\Engine $engine)
     {
         // Time Stats
        $end = $engine->getMilliseconds();
        $runtime = round(($end - $engine->start_time) / 1000, 4);
        
        // Tests Run
        $tests = array_merge(
            $engine->getSuites(),
            array_filter($engine->getTests(), function($test){
                if ($test->getSuite() === null) return true;
                return false;
            })
        );
         
        // Test Pass/Fail counts
        $number_of_passed_tests = 0;
        $number_of_failed_tests = 0;
        $number_of_skipped_tests = 0;
        $number_of_tests_total = 0;
        $number_of_suites_total = 0;
         
        // Assertion pass/fail/skip counts
        $number_of_failed_assertions = 0;
        $number_of_passed_assertions = 0;
        $number_of_skipped_assertions = 0;
        $number_of_total_assertions = 0;
        
        // Final of all tests which ran
        $final_tests = array();
        
        $_suite = null;
        foreach ($tests as $_test) {
            // is this a suite or a test?
            if ($_test instanceof \prggmrunit\Suite) {
                $_suite = $_test;
                $_test = $_suite->test();
                $number_of_tests_total += $_suite->getTestCount();
                $number_of_suites_total++;
            } else {
                $number_of_tests_total++;
            }
            // how?
            if (null === $_test) continue;
            // Get test result
            switch($_test->getTestResult()) {
                case \prggmrunit\Test::FAIL:
                    $number_of_failed_tests++;
                    break;
                case \prggmrunit\Test::PASS:
                    $number_of_passed_tests++;
                    break;
                case \prggmrunit\Test::SKIP:
                    $number_of_skipped_tests++;
                    break;
            }
            // get assertion results
            $number_of_failed_assertions += $_test->failedAssertions();
            $number_of_passed_assertions += $_test->passedAssertions();
            $number_of_skipped_assertions += $_test->skippedAssertions();
            
            // get messages
            $messages = $_test->getTestMessages();
            if (is_array($messages)) {
                foreach ($messages as $_key => $_message) {
                    if (!isset($this->_messages[$_key])) {
                        $this->_messages[$_key] = array();
                    }
                    $this->_messages[$_key][] = $_message;
                }
            }
            
            // add this test to final tests
            $this->_tests[] = $_test;            
        }
        
        // Compile everything
        $this->_results = array(
            'passed_tests'       => $number_of_passed_tests,
            'failed_tests'       => $number_of_failed_tests,
            'skipped_tests'      => $number_of_skipped_tests,
            'tests_total'        => $number_of_tests_total,
            'suites_total'       => $number_of_suites_total,
            'failed_assertions'  => $number_of_failed_assertions,
            'passed_assertions'  => $number_of_passed_assertions,
            'skipped_assertions' => $number_of_skipped_assertions,
            'total_assertions'   => $number_of_total_assertions,
            'runtime'            => $runtime,
            // this is only a rough estimate
            'memory_usage'       => round(memory_get_peak_usage(true), 4)
        );
    }
    
    /**
     * Returns tests used for results calculations.
     *
     * @return  array
     */
    public function getTests()
    {
        return $this->_tests;
    }
     
     /**
      * Returns testing messages encountered.
      *
      * @return  array
      */
    public function getMessages()
    {
        return $this->_messages;
    }
    
    /**
     * Returns results data.
     *
     * @return  mixed
     */
    public function __call($name, $args) 
    {
        // keep the original
        $original = $name;
        $data = explode(' ', preg_replace('/([a-z0-9])?([A-Z])/','$1 $2', $name));

        // get is optional
        if ($data[0] == 'get') {
            array_shift($data);
        }
        
        $original = $name;
        
        if ($data[0] == 'all') {
            return $this->_results;
        }
        
        $name = implode('_', array_map('strtolower', $data));
        
        if (!isset($this->_results[$name])) {
            $suggestions = array_filter($this->avaliableResults, function($var) use ($original){
                if (\similar_text($var, $original) >= 8) return true;
                return false;
            });
            trigger_error(sprintf(
                "%s is an unknown result maybe you want %s?",
                $original,
                (count($suggestions) == 0) ? 
                    implode(", ", $this->avaliableResults) :
                    implode(", ", $suggestions)
            ), E_USER_NOTICE);
        } else {
            return $this->_results[$name];
        }
    }
}
