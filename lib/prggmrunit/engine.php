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
 * Prggmrunit object is designed around the prggmr
 * engine to allow adoption of unit testing with events.
 */
class Engine extends \prggmr\Engine {
    
    /**
     * Array of avaliable assertion tests.
     *
     * @var  array
     */
    protected $_assertions = array();
    
    /**
     * Array of tests.
     *
     * @var  array
     */
    protected $_tests = array();
    
    /**
     * Number of intervals set.
     *
     * @var  integer
     */
    protected $_intervals = 0;
    
    /**
     * Adds a new assertion test to run within a unit test.
     *
     * @param  object  $closure  Callable php function
     * @param  string  $name  Test name
     */
    public function assertion($closure, $name)
    {
        $this->_assertion[$name] = $closure;
    }
    
    /**
     * Runs an assertion.
     *
     * @param  string  $assertion  Assertion test
     * @param  array  $params  Parameters
     * 
     * @return  boolean
     */
    public function assert($name, $params)
    {
        if (isset($this->_assertion[$name])) {
            return call_user_func_array($this->_assertion[$name], $params);
        } else {
            return null;
        }
    }
    
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
        if (null === $event || is_object($event) && !$event instanceof Test) {
            $event = new Test();
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
        $this->setInterval(function($e) use (&$engine){
            // shutdown if the interval timers are all thats left
            if (count($engine->countTimers()) <= $engine->countIntervals()) {
                $engine->shutdown();
            }
        }, 10, null, 'Killswitch');
        // START
        $this->fire(Events::START, $this);
        // Start the daemon
        $this->daemon($reset, $timeout);
        // END
        $this->fire(Events::END, $this);
    }
    
    /**
     * Modifies setInterval to allow tracking of the number of intervals set.
     *
     * @todo  This should be an addition to the prggmr engine, which also
     * includes timeouts and normal subscriptions.
     */
    public function setInterval($subscription, $interval, $vars = null, $identifier = null, $exhaust = 0)
    {
        $this->_intervals++;
        parent::setInterval($subscription, $interval, $vars, $identifier, $exhaust);
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
    public function getTests()
    {
        return $this->_tests;
    }
}