<?php
/**
 * prggmrunit Unit Tests
 */

// equals
test('equal', function($test){
    $test->equal('no', 'no');
});

// null
test('null', function($test){
    $test->null(null);
});

// true
test('true', function($test){
    $test->true(true);
});

// false
test('false', function($test){
    $test->false(false);
});

// exception
test('exception', function($test){
    $test->exception('InvalidArgumentException', function(){
       throw new \InvalidArgumentException();
    });
});

// array
test('array', function($test){
   $test->array(array());
});

// string
test('string', function($test){
    $test->string('string');
});

// integer
test('integer', function($test){
   $test->integer(10);
});

// float
test('float', function($test){
   $test->float(10.5);
});

// object
test('object', function($test){
   $test->object(new \stdClass());
});

// instanceof
test('instanceof', function($test){
   $test->instanceof(new \prggmr\Engine(), 'prggmr\Engine');
});

test('event', function($test){
    subscribe('test-event', function($event){
        $event->setData('test');
    });
    $test->event('test-event', array('test'));
});