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
 * prggmrunit is a unit testing suite for php
 */
$dir = dirname(realpath(__FILE__));
require $dir.'/../prggmr/lib/prggmr.php';

if (version_compare(\Prggmr::version(), 'v0.2.0', '<=')) {
    exit('prggmrunit requires prggmr v0.2.0');
}

// library version
define('PRGGMRUNIT_VERSION', 'v0.1.0');

// prggmunit events
class Events {
    const START = 'prggmrunit_start';
    const TEST  = 'prggmrunit_test';
    const END   = 'prggmrunit_end';
    const SUITE_STARTUP = 'prggmrunit_suite_startup';
    const SUITE_SHUTDOWN = 'prggmrunit_suite_shutdown';
}

class Test extends \prggmr\Event {

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
    public function test($test, $msg)
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
            $this->_failures[$current][] = $msg;
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
     * Returns the break stastus.
     *
     * @return array
     */
    public function getRuns()
    {
        return $this->_run;
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

    public function test($name, $test)
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
$event = new Test();
$engine = new \prggmr\Engine();

/**
 * equals test
 */
$event->addTest(function($test, $expect, $actual){
    $test->test(($expect === $actual), sprintf(
        "Failed asserting %s equals %s",
        print_r($actual, true),
        print_r($expect, true)
    ));
}, 'equal');

/**
 * event test
 */
$event->addTest(function($test, $signal, $expected, $params = null, $event = null, $engine = null){
    if (null !== $engine) {
        $fire = $engine->fire($signal, $params, $event);
    } else {
        $fire = \Prggmr::instance()->fire($signal, $params, $event);
    }
    $test->test(($fire->getData() === $expected), sprintf(
        "Failed asserting event data %s equals %s",
        print_r($fire->getData(), true),
        print_r($expected, true)
    ));
}, 'event');

/**
 * exception test
 */
$event->addTest(function($test, $exception, $code){
    try {
        $code();
    } catch (\Exception $e) {
        $test->test((get_class($e) === $exception), sprintf(
            'Exception %s was thrown expected %s',
            get_class($e),
            $exception
        ));
        return true;
    }
    $test->test(false, 'Exception not thrown');
}, 'exception');

/**
 * true
 */
$event->addTest(function($test, $var){
    $test->test(($var === true), sprintf(
        'Failed asserting %s equals true',
        print_r($var, true)
    ));
}, 'true');

/**
 * false
 */
$event->addTest(function($test, $var){
    $test->test(($var === false), sprintf(
        'Failed asserting %s equals false',
        print_r($var, true)
    ));
}, 'false');

/**
 * null
 */
$event->addTest(function($test, $var){
    $test->test(($var === null), sprintf(
        'Failed asserting %s equals null',
        print_r($var, true)
    ));
}, 'null');

/**
 * array
 */
$event->addTest(function($test, $array){
    $test->test((is_array($array)), sprintf(
        'Failed asserting %s is an array',
        gettype($array)
    ));
}, 'array');

/**
 * string
 */
$event->addTest(function($test, $string){
    $test->test((is_string($string)), sprintf(
        'Failed asserting %s is a string',
        gettype($array)
    ));
}, 'string');

/**
 * integer
 */
$event->addTest(function($test, $int){
    $test->test((is_int($int)), sprintf(
        'Failed asserting %s is an integer',
        gettype($array)
    ));
}, 'integer');

/**
 * float
 */
$event->addTest(function($test, $float){
    $test->test((is_float($float)), sprintf(
        'Failed asserting %s is a float',
        gettype($array)
    ));
}, 'float');

/**
 * object
 */
$event->addTest(function($test, $object){
    $test->test((is_object($object)), sprintf(
        'Failed asserting %s is an object',
        gettype($array)
    ));
}, 'object');

/**
 * instanceof
 */
$event->addTest(function($test, $class, $object){
    $test->test((get_class($class) === $object), sprintf(
        'Failed asserting %s is an instance of %s',
        get_class($class),
        $object
    ));
}, 'instanceof');

$GLOBALS['_PRGGMRUNIT_EVENT'] = $event;
$GLOBALS['_PRGGMRUNIT_ENGINE'] = $engine;

$engine->subscribe(Events::START, function($event){
    $event->setData(\Prggmr::instance()->getMilliseconds(), 'start_time');
    echo "prggmrUnit ".PRGGMRUNIT_VERSION."\n\n";
});

$engine->subscribe(Events::END, function($test){
    $runtime = round((\Prggmr::instance()->getMilliseconds() - $test->getData('start_time')) / 1000, 4);
    $testCount = $test->testCount();
    $pass = $test->passed();
    $passTests = 0;
    $passAssertions = 0;
    foreach ($pass as $_pass) {
        $passTests++;
        $passAssertions += count($_pass);
    }
    $fail = $test->failures();
    $failTests = 0;
    $failAssertions = 0;
    foreach ($fail as $_fail) {
        $failTests++;
        $failedAssertions += count($_fail);
    }
    $assertions = $test->assertionCount();
    if (0 != count($fail)) {
        echo "\n\n----------------------------------";
        echo "\nFailures Detected\n";
        foreach ($fail as $_k => $_fail) {
            echo "\n----------------------------------";
            echo sprintf("\nTest ( %s ) had ( %s ) failures\n", $_k, count($_fail));
            for ($i=0;$i!=count($_fail);$i++){
                echo "".($i+1).". ".$_fail[$i]."\n";
            }
        }
        echo "\n\n";
    }

    $size = function($size) {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    };

    echo "\nRan $testCount tests in $runtime seconds and used ".$size(round(memory_get_peak_usage(true), 4));
    echo "\n\n";
    if ($failTests != 0) {
        echo sprintf("FAIL (%s)", $failTests);
    } else {
        echo sprintf("PASS");
    }
    echo "\nAssertions $passAssertions/".$test->assertionCount()."\n";
});

// check if there are any valid tests to run!
$engine->subscribe(Events::TEST, function($event){
    if ($GLOBALS['_PRGGMRUNIT_ENGINE']->queue(Events::TEST)->count() == 2) {
        exit("Failed to recieve any tests to run!\n");
    }
}, 'hasTestsCheck', 0, null, 1);