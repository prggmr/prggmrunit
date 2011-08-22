<?php
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
    return Prggmrunit::instance()->test($test, $name, $repeat, $event);
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
    return new \prggmrunit\Suite($test, Prggmrunit::instance());
}