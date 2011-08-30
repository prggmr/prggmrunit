<?php
/**
 * prggmrunit
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
   $test->instanceof(new \prggmr\Engine(), 'prggmr\Engine');
}, 'instanceof');


test(function($test){
    $test->skip();
    $test->false(true);
    $test->equals('a', 'a');
    $test->true(false);
    $test->endSkip();
    $test->true(false);
});