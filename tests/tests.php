<?php
/**
 * prggmrunit
 */

test('equal', function($test){
    $test->equal('no', 'no');
});

test('null', function($test){
    $test->null(null);
});

test('true', function($test){
    $test->true(true);
});

test('false', function($test){
    $test->false(false);
});

test('exception', function($test){
    $test->exception('InvalidArgumentException', function(){
       throw new \InvalidArgumentException();
    });
});

test('array', function($test){
   $test->array(array());
});

test('string', function($test){
    $test->string('string');
});

test('integer', function($test){
   $test->integer(10);
});

test('float', function($test){
   $test->float(10.5);
});

test('object', function($test){
   $test->object(new \stdClass());
});

test('instanceof', function($test){
   $test->instanceof(new \prggmr\Engine(), 'prggmr\Engine');
});

test('event', function($test){
    subscribe('test-event', function($event){
        $event->setData('test');
        $event->setData('one', 'two');
    });
    $test->event('test-event', array('test', 'two' => 'one'));
});
