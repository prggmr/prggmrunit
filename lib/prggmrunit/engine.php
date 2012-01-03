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
 * prggmrunit object is designed around the prggmr
 * engine to allow adoption of unit testing with events.
 */
class Engine extends \prggmr\Engine {
    
    /**
     * Array of tests.
     *
     * @var  array
     */
    protected $_tests = array();
    
    /**
     * Array of suites.
     *
     * @var  array 
     */
    protected $_suites = array();
    
    /**
     * Assertions object
     * 
     * @var  object
     */
    protected $_assertions = null;
    
    /**
     * Number of intervals set.
     *
     * @var  integer
     */
    protected $_intervals = 0;
    
    /**
     * Current testing suite.
     *
     * @var  object
     */
    protected $_suite = null;
    
    /**
     * End of test results
     *
     * @var  object
     */
    protected $_results = null;
    
    /**
     * Adds a new unit test.
     *
     * @param  closure  $test  Test contained within a closure.
     * @param  string  $name  Name of the test.
     * @param  integer  $repeat  Number of times to repeat this test.
     * @param  object  \prggmrunit\Test
     *
     * @return  void
     */
    public function test($test, $name = null, $repeat = 1, $event = null)
    {    
        if (!$test instanceof \prggmr\Subscription) {
            if (is_callable($test)) {
                $test = new \prggmr\Subscription(
                    $test, $name, $repeat
                );
            } else {
                throw new \InvalidArgumentException(
                    'prggmrunit\Engine::test expects a closure
                    or \prggmr\Subscription object'
                );
            }
        }
        
        if (null !== $this->_suite) {
            $this->_suite->addTest();
            $event = $this->_suite->test();
            if ($this->_suite->setUp() != null) {
                $test->preFire($this->_suite->setUp());
            }
            if ($this->_suite->tearDown() != null) {
                $test->postFire($this->_suite->tearDown());
            };
        } elseif (null === $event || !$event instanceof Test) {
            $event = new Test($this->_assertions);
        } 
        
        $sub = $this->setTimeout(
            $test,
            0,
            $event,
            $test->getIdentifier()
        );
        
        $this->_tests[$test->getIdentifier()] =& $event;
        return $sub;
    }
    
    /**
     * Runs testing framework.
     *
     * @param  boolean  $reset  Resets all timers to begin at daemon start.
     * @param  integer  $timeout  Number of milliseconds to run the daemon.
     *
     * @return  void
     */
    public function run($reset = false, $timeout = null)
    {
        // set an interval to determain if anymore tests to run and shutdown
        // really ... this needs to be fixed 
        $engine = $this;
        $this->setInterval(function() use (&$engine){
            // shutdown if the interval timers are all thats left
            if (count($engine->countTimers()) <= $engine->countIntervals()) {
                $engine->shutdown();
            }
        }, 10, null, 'Killswitch');
        // START
        $this->fire(Events::START, $this);
        $this->start_time = $this->getMilliseconds();
        // Start the loop
        $this->loop($reset, $timeout);
        // DETERMAIN RESULTS
        $this->_statResults();
        // END
        $this->fire(Events::END, $this);
    }
    
    /**
     * Modifies setInterval to allow tracking of the number of intervals set.
     *
     * @todo  This should be an addition to the prggmr engine, which also
     * includes timeouts and normal subscriptions.
     */
    public function setInterval($subscription, $interval, $vars = null, $identifier = null, $exhaust = 0, $start = null)
    {
        $this->_intervals++;
        parent::setInterval($subscription, $interval, $vars, $identifier, $exhaust, $start);
    }
    
    /**
     * Returns a count of intervals set.
     *
     * @return  integer
     */
    public function countIntervals(/* ... */)
    {
        return $this->_intervals;
    }
    
    /**
     * Returns the tests array.
     *
     * @return  array
     */
    public function getTests(/* ... */)
    {
        return $this->_tests;
    }
    
    /**
     * Sets the current testing suite.
     *
     * Providing blank or null resets.
     *
     * @param  object  $suite  \prggmrunit\Suite
     *
     * @return  void
     */
    public function suite(\prggmrunit\Suite $suite = null)
    {
        // set as active
        if (null === $suite) {
            $this->_suite = null;
        } else {
            $this->_suite = $suite;
        }
        // add to suites
        $this->_suites[] = $suite;
    }
    
    /**
     * Gets the current suite.
     *
     * @return  object
     */
    public function getSuite(/* ... */)
    {
        return $this->_suite;
    }
    
    /**
     * Sets the assertion object.
     * 
     * @param  object  $assertion  \prggmrunit\Assertions
     */
     public function setAssertions($assertions)
     {
         $this->_assertions = $assertions;
     }
     
     /**
      * Returns the assertion object.
      * 
      * @return  object  \prggmrunit\Assertions
      */
      public function getAssertions()
      {
          return $this->_assertions;
      }
      
      /**
       * Generates the results of testing run.
       *
       * @return  void
       */
       public function _statResults()
       {
           $this->_results = new \prggmrunit\Results($this);
       }
       
       /**
        * Returns the results object.
        *
        * @return  object  \prggmrunit\Results
        */
        public function getResults()
        {
            return $this->_results;
        }
        
       /**
        * Returns all suites.
        *
        * @return  array  
        */
        public function getSuites()
        {
            return $this->_suites;
        }
          
}
