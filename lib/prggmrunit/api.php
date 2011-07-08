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
 * Tests are performed as anoymous functions as follows.
 *
 * test('mytest', function($test){
 *     $test->equals('a', 'a');
 *     $test->true((true == 1));
 *     $test->false((false == 0));
 * });
 *
 * @param  string  $name  Name of the unit test
 * @param  object  $test  Test function
 */
function test($name, $test) {
    $GLOBALS['_PRGGMRUNIT_EVENT']->_current = $name;
    $GLOBALS['_PRGGMRUNIT_EVENT']->addRun($name);
    $GLOBALS['_PRGGMRUNIT_ENGINE']->subscribe(\prggmrunit\Events::TEST, $test, $name);
}

/**
 * Creates a new testing suite.
 */
function suite($name, $test) {
    $suite = clone $GLOBALS['_PRGGMRUNIT_EVENT'];
    $engine = $GLOBALS['_PRGGMRUNIT_ENGINE'];
    $engine->subscribe(\prggmrunit\Events::TEST, $test, $name);
}

// strange??
test(null, function(){;});