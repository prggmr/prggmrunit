<?php
/**
 *  Copyright 2010-12 Nickolas Whiting
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
 * @copyright  Copyright (c), 2010-12 Nickolas Whiting
 */

/**
 * prggmrunit engine test
 */
suite(function(){
    
    setup(function($test){
        $test->engine = new \prggmrunit\Engine();
    });

    teardown(function($test){
        $test->engine = null;
    });

    test(function($test){
        $test->array($test->engine->getTests());
        $test->null($test->engine->getAssertions());
        $test->equal(0, count($test->engine->getTests()));
        $test->null($test->engine->getSuite());
    }, 'Construction Test');
    
    test(function($test){
        $test->equal(0, count($test->engine->getTests()));
        $test->engine->test(function(){}, 'Null Test');
        $tests = $test->engine->getTests();
        $test->array($tests);
        $test->equal(1, count($tests));
        $test->exception('InvalidArgumentException', function() use ($test){
            $test->engine->test('uncallable');
        });
    }, 'Adding Tests');

});
