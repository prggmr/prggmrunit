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
 * This tests all standard assertions
 */

test(function($test){
    $test->equal('no', 'no');
}, 'equal');

test(function($test){
    $test->null(null);
}, 'null');

test(function($test){
    $test->true(true);
}, 'true');

test(function($test){
    $test->false(false);
}, 'false');

test(function($test){
    $test->exception('InvalidArgumentException', function(){
       throw new \InvalidArgumentException();
    });
}, 'exception');

test(function($test){
   $test->array(array());
}, 'array');

test(function($test){
    $test->string('string');
}, 'string');

test(function($test){
   $test->integer(10);
}, 'integer');

test(function($test){
   $test->float(10.5);
}, 'float');

test(function($test){
   $test->object(new \stdClass());
}, 'object');

test(function($test){
   $test->instanceof('prggmr\Engine', new \prggmr\Engine());
}, 'instanceof');


