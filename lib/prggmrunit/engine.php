<?php
namespace prggmrunit;
/**
 *  Copyright 2010 Nickolas Whiting
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
 * @author  Nickolas Whiting  <me@nwhiting.com>
 * @package  prggmrunit
 * @copyright  Copyright (c), 2010 Nickolas Whiting
 */

/**
 * prggmrunit engine
 */
class Engine extends \prggmr\Engine {

    // failed tests
    protected $_failures = array();

    // passed tests
    protected $_pass = array();

    // tests that have ran
    public $_run = array();

    // number of assertions run
    protected $_count = 0;

    // tests avaliable
    protected $_tests = array();

    // provides a line break after x amount of tests result prints
    protected $_break = array(
        'count' => 0,
        'pass'  => 0,
        'fail'  => 0
    );

    // number of passed assertions
    protected $_assertionPass = 0;

    // number of failed assertions
    protected $_assertionFail = 0;

    /**
     * Runs a unit test and prints the results
     *
     * @param  boolean  $test
     * @param  string  $msg  Failure message
     */
    public function test($test, $msg = null)
    {
        $this->_count++;
        $this->_break['count'] += 1;
        $current = $this->getSubscription()->getIdentifier();
        if (false === $test) {
            if (!isset($this->_failures[$current])) {
                $this->_failures[$current] = array();
            }
            $this->_assertionFail++;
            $this->_break['fail']++;
            $trace = debug_backtrace(false);
            $this->_failures[$current][] = array(
                'message' => $msg,
                'trace' => $trace[0]
            );
            echo "F";
        } else {
            if (!isset($this->_pass[$current])) {
                $this->_pass[$current] = array();
            }
            $this->_assertionPass++;
            $this->_break['pass']++;
            $this->_pass[$current][] = true;
            echo ".";
        }
        if ($this->_break['count'] >= 60) {
            echo sprintf(" [%s/%s]\n", $this->_break['pass'], ($this->_break['pass'] + $this->_break['fail']));
            $this->_break = array(
                'count' => 0,
                'pass'  => 0,
                'fail'  => 0
            );
        }
    }

    public function __call($name, $args)
    {
        if (isset($this->_tests[$name])) {
            array_unshift($args, $this);
            call_user_func_array($this->_tests[$name], $args);
        }
    }

    /**
     * Adds a new assertion test to run within a unit test.
     *
     * @param  object  $closure  Callable php function
     * @param  string  $name  Test name
     */
    public function addTest($closure, $name)
    {
        $this->_tests[$name] = $closure;
    }

    /**
     * Returns a count of the total tests ran.
     *
     * @return  integer
     */
    public function testCount()
    {
        return count($this->_run);
    }

    /**
     * Returns a count of the total assertions.
     *
     * @return  integer
     */
    public function assertionCount()
    {
        return $this->_count;
    }

    /**
     * Returns a count of the failed tests.
     *
     * @return  integer
     */
    public function failures()
    {
        return $this->_failures;
    }

    /**
     * Returns a count of the passed tests.
     *
     * @return  integer
     */
    public function passed()
    {
        return $this->_pass;
    }

    /**
     * Returns a count of passed assertions.
     *
     * @return  integer
     */
    public function failedAssertions()
    {
        return $this->_assertionFail;
    }

    /**
     * Returns a count of passed assertions.
     *
     * @return  integer
     */
    public function passedAssertions()
    {
        return $this->_assertionPass;
    }

    /**
     * Adds a new test run.
     *
     * @return  integer
     */
    public function addRun($name)
    {
        $this->_run[] = $name;
    }

    /**
     * Returns the ran tests.
     *
     * @return array
     */
    public function getRuns()
    {
        return $this->_run;
    }

    /**
     * Returns the break status, used for suite testing.
     *
     * @return array
     */
    public function getBreaks()
    {
        return $this->_break;
    }

    /**
     * Adds assertion count.
     *
     * @param  integer  $count  Assertion count
     */
    public function addAssertion($count = null)
    {
        // default add
        if (null === $count) $this->test(true);
        $this->test(true);
    }

    /**
     * Cloning resets all counts to allow combine at a later point.
     */
    public function __clone()
    {
        $this->_failures = array();
        $this->_pass = array();
        $this->_run = array();
        $this->_count = 0;
        $this->_assertionPass = 0;
        $this->_assertionFail = 0;
        $this->_break = $this->getBreaks();
    }

    /**
     * Combines a test result set.
     *
     * @param  object  $test  Test
     */
    public function combine(Test $test)
    {
        $this->_failures += $test->failures();
        $this->_pass += $test->passed();
        $this->_run = array_merge($this->_run, $test->getRuns());
        $this->_count += $test->assertionCount();
        $this->_assertionFail += $this->failedAssertions();
        $this->_assertionPass += $this->passedAssertions();
        $this->_break = $test->getBreaks();
    }
}

class Suite {

    // name of the suite
    protected $_suite = null;

    // test event
    protected $_test = null;

    // startup function
    protected $_setup = null;

    // teardown function
    protected $_teardown = null;

    public function __construct($suite, Test $test)
    {
        $this->_engine = new \prggmr\Engine();
        $this->_suite = $suite;
        $this->_test = $test;
        $GLOBALS['_PRGGMRUNIT_ENGINE']->fire(Events::SUITE_STARTUP, array(
            $suite, $this
        ));
    }

    public function setUp($function)
    {
        $this->_setup = $function;
    }

    public function tearDown($function)
    {
        $this->_teardown = $function;
    }

    public function __call($name, $args)
    {
        $this->_test->_call($name, $args);
    }

    public function run()
    {
        $this->_engine->fire(Events::TEST, null, $this->_test);
        $GLOBALS['_PRGGMRUNIT_ENGINE']->fire(Events::SUITE_SHUTDOWN, array(
            $suite, $this
        ));
        return $this->_test;
    }

    public function test($test, $name = null)
    {
        $this->_test->addRun($name);
        $subscription = new \prggmr\Subscription($test, $name);
        if (null !== $this->_setup) {
            $subscription->preFire($this->_setup);
        }
        if (null !== $this->_teardown) {
            $subscription->postFire($this->_teardown);
        }
        $this->_engine->subscribe(\prggmrunit\Events::TEST, $subscription);
    }
}

// our main event
$event = $GLOBALS['_PRGGMRUNIT_EVENT'] = new Test();
$engine = $GLOBALS['_PRGGMRUNIT_ENGINE'] = new \prggmr\Engine();