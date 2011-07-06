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
require '../prggmr/lib/prggmr.php';

if (version_compare(\Prggmr::version(), 'v0.2.0', '<=')) {
    exit('prggmrunit requires prggmr v0.2.0');
}

// library version
define('PRGGMRUNIT_VERSION', 'v0.1.0');

class Events {
    const START = 'prggmrunit_start';
    const TEST  = 'prggmrunit_test';
    const END   = 'prggmrunit_end';
}

class Test extends \prggmr\Event {
    
    // failed tests
    protected $_failures = array();
    
    // passed tests
    protected $_pass = array();
    
    // tests that have ran
    protected $_run = array();
    
    // number of assertions run
    protected $_count = 0;
    
    // tests avaliable
    protected $_tests = array(); 
    
    public function test($test, $msg)
    {
        $current = $this->getSubscription()->getIdentifier();
        if (false === $test) {
            if (!isset($this->_failures[$current])) {
                $this->_failures[$current] = array();
            }
            $this->_failures[$current][] = $msg;
            echo "F";
        } else {
            $this->_count++;
            if (!isset($this->_pass[$current])) {
                $this->_pass[$current] = array();
            }
            $this->_pass[$current][] = true;
            echo ".";
        }
    }
    
    public function __call($name, $args)
    {
        if (isset($this->_tests[$name])) {
            array_unshift($args, $this);
            call_user_func_array($this->_tests[$name], $args);
        }
    }
    
    // adds a new avaliable test
    public function addTest($closure, $name)
    {
        $this->_tests[$name] = $closure;
    }
    
    public function testCount()
    {
        return count($this->_run);
    }
    
    public function assertionCount()
    {
        return $this->_count;
    }
    
    public function failures()
    {
        return $this->_failures;
    }
    
    public function passed()
    {
        return $this->_pass;
    }
    
    public function addRun($name)
    {
        $this->_run[] = $name;
    }
}

// our main event
$event = new Test();
$engine = new \prggmr\Engine();
// default tests
$event->addTest(function($event, $expect, $actual){
    $event->test(($expect === $actual), sprintf(
        "Failed asserting %s equals %s",
        print_r($expect, true),
        print_r($actual, true)
    ));
}, 'equal');

$GLOBALS['_PRGGMRUNIT_EVENT'] = $event;
$GLOBALS['_PRGGMRUNIT_ENGINE'] = $engine;

// creates a new test
function test($name, $test) {
    $GLOBALS['_PRGGMRUNIT_EVENT']->_current = $name;
    $GLOBALS['_PRGGMRUNIT_EVENT']->addRun($name);
    $GLOBALS['_PRGGMRUNIT_ENGINE']->subscribe(Events::TEST, $test, $name);
}

$engine->subscribe(Events::START, function($event){
    $event->setData(\Prggmr::instance()->getMilliseconds(), 'start_time');
    echo "prggmrUnit ".PRGGMRUNIT_VERSION."\n\n";
});

$engine->subscribe(Events::END, function($test){
    
    $runtime = \Prggmr::instance()->getMilliseconds() - $test->getData('start_time');
    $testCount = $test->testCount();
    $pass = $test->passed();
    $passTests = 0;
    $passAssertions = 0;
    foreach ($pass as $_pass) {
        $passTests++;
        $passAssertions += count($pass);
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
        echo "\n----------------------------------";
        echo "\nFailures Detected\n";
        foreach ($fail as $_k => $_fail) {
            echo sprintf("Test %s had %s failures\n", $_k, count($_fail));
            echo implode("\n", $_fail);
        }
    }
    
    $size = function($size) {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
        return $size ? round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $filesizename[$i] : '0 Bytes';
    };
    
    echo "\n----------------------------------";
    echo "\nRan $testCount tests in $runtime seconds and used ".$size(round(memory_get_peak_usage(true), 4));
    echo "\n\n";
    //echo "Total Assertions $assertions\n";
    //echo "Tests Passed $passTests\n";
    //echo "Tests Failed $failTests\n";
    if ($failTests != 0) {
        echo sprintf("FAIL (%s) failures", $failTests);
    } else {
        echo sprintf("PASS");
    }
    echo " Assertions $passAssertions/".$test->assertionCount();
});

test('test', function($test){
    $test->equal(array('a'), array('ab'));
});

test('another', function($test){
    $test->equal('no', 'no');
});

test('andagain', function($t){
    $t->equal(array('asdf'), array('asdf'));
});

$engine->fire(Events::START,null,$GLOBALS['_PRGGMRUNIT_EVENT']);
$engine->fire(Events::TEST,null,$GLOBALS['_PRGGMRUNIT_EVENT']);
$engine->fire(Events::END,null,$GLOBALS['_PRGGMRUNIT_EVENT']);