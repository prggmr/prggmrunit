<?php
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
 * Creates a new prggmrunit test.
 *
 * Tests are anonymous functions which consist of a group of assertions.
 *
 * Usage:
 *
 * test(function($test){
 *     $test->equals('a', 'a');
 *     $test->true((true == 1));
 *     $test->false((false == 0));
 * }, 'name');
 *
 * @param  object  $test  Test function
 * @param  string  $name  Name of the unit test
 */
function test($test, $name = null, $repeat = 1, $event = null) {
    return prggmrunit::instance()->test($test, $name, $repeat, $event);
}

/**
 * Creates a new testing suite.
 *
 * Suites are a group of tests which ease the use of startup and shutdown.
 *
 * Usage:
 *
 * suite(function($suite){
 *
 *     $suite->setUp(function($suite){
 *          $suite->db = new PDO('sqlite:db.sqlite');
 *     });
 *
 *     $suite->test(function($suite){
 *          $suite->equals('one', 'one');
 *          $suite->integer(25);
 *     });
 *
 * }, 'suite-name);
 */
function suite($test) {
    return new \prggmrunit\Suite($test, prggmrunit::instance());
}

/**
 * Registers the setup function for the current suite.
 *
 * @param  closure  $closure
 *
 * @return  void
 */
function setup($function) {
    if (null !== prggmrunit::instance()->getSuite()) {
        prggmrunit::instance()->getSuite()->setup($function);
    }
}

/**
 * Registers the teardown function for the current suite.
 *
 * @param  closure  $closure
 *
 * @return  void
 */
function teardown($function) {
    if (null !== prggmrunit::instance()->getSuite()) {
        prggmrunit::instance()->getSuite()->teardown($function);
    }
}

/**
 * Registers a new assertion for use when testing.
 *
 * @param  closure  $function  Anonymous function used to evaluate assertion.
 * @param  name  $name  Name that will be used to call this assertion.
 *
 * @throws  InvalidArgumentException
 * 
 * @return  void
 */
function assertion($function, $name) {
    return prggmrunit::instance()->assertion($function, $name);
}