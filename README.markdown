# prggmrunit

high-performance evented unit testing for PHP 5.3+

## Introduction

prggmrunit is a unit testing library built using the prggmr library, it
is designed to be incredibly simple, intuitive and lighting fast.

prggmrunit was developed from the hassle of the currently avaliable testing suites being oversized,
problamatic and featuring unituitive API's.

## Install

This is the only current method of installation an installation script will be written soon enough.

    cd "location for your install"
    git clone git@github.com:prggmrlabs/prggmrunit.git
    git submodule init
    git submodule update
    chmod +x prggmrunit
    sudo ln -s prggmrunit /usr/local/bin/

## Usage

prggmrunit breaks testing into two groups.

### Ordinary

Ordinary tests are tests which are run independent of a suite.

ex:

```php
test(function($test){
    $test->equals('a', 'a');
    $test->null(null);
}, 'example');
```


### Suites

Suite are a grouping of tests run together allowing for a setUp and tearDown events before and after each test is fun.

### ex:

```php
suite(function($suite){

    // setup
    $suite->setUp(function($suite){
        // init db connection
        $suite->db = 'my_db_connection';
    });

    // teardown
    $suite->tearDown(function($suite){
        // reset db connection
        $suite->db = null;
        if (isset($suite->tear)) unset($suite->tear);
    });

    $suite->test(function($suite){
        // is our db connection real?
        $suite->equals($suite->db, 'my_db_connection');
        $suite->tear = 'true';
    }, 'test_my_db');

    $suite->test(function($suite){
        $suite->false(isset($suite->tear));
    });

}, 'example-suite');
```

## Assertions

prggmrunit provides the following methods for assertions in your unit tests.

### equals

Asserts that $expected is strictly equal to $actual.

```php
test(function($test){
    $test->equals(expected, actual);
});
```

### true($var)

Asserts the given variable or expression equals true.

```php
test(function($test){
    $test->true(expression);
});
```

### false

Asserts the given variable or expression equals false.

```php
test(function($test){
    $test->false(expression);
});
```

### null

Asserts the given variable or expression equals null.

```php
test(function($test){
    $test->null(expression);
});
```

### array

Asserts the given variable is an array.

```php
test(function($test){
    $test->array(variable);
});
```

### string

Asserts the given variable is a string.

```php
test(function($test){
    $test->string(string);
});
```

### integer

Asserts the given variable is an integer.

```php
test(function($test){
    $test->integer(variable);
});
```

### float

Asserts the given variable is a float.

```php
test(function($test){
    $test->float(variable);
});
```

### object

Asserts the given variable is an object.

```php
test(function($test){
    $test->object(variable);
});
```

### instanceof($var)

Asserts the given variable is an instance of a specific object.

```php
test(function($test){
    $test->instanceof(object, expected);
});
```

#### event

Asserts the given event signal result data equals expected result

```php
test(function($test){
    $test->event(signal, expected[, params, [event, [engine]]]);
});
```

## Custom assertion tests

prggmrunit allows for the creation of custom assertions

### ex:

```php
// pull the test event object
$event = $GLOBALS['_PRGGMRUNIT_EVENT']

// custom assertion tests are added using "addTest" method
// you provide the function as the first parameter
// the name as the second
// the first parameter provided to your custom assertion function will
// allways be the test event object, followed by the parameters provided
// from the assertion call.
$event->addTest(function(test, param1, param2, param3, etc...){
    // perform logic here

    // the assertion is added to the count using the "test" function
    // which expects the first parameter to be either "true|false"
    // with the second parameter a failure message.
    $test->test(true|false eval, 'failure message');
}, 'nameofmethod');
```


The below example creates a custom assertion function which validates the provided string equals "helloworld".

### usage:

```php
$event = $GLOBALS['_PRGGMRUNIT_EVENT'];

$event->addTest(function($test, $string){

    if (strtolower($string) != 'helloworld') {
        $test->test(false, sprintf(
            'String %s does not equal helloworld',
            $string
        ));
    } else {
        $test->test(true);
    }

}, 'helloworld');

// test assertion
test(function($test){
    $test->helloworld('HelloWorld');
});
```

### Example Output

#### Passed

    prggmrUnit v0.1.0

    .......... ( Indication of current tests statuses [ . = pass, F = failure ] )
    Ran 11 tests in 2 seconds and used 1.5 MB ( Stats of the run )

    PASS ( Result status )
    Assertions 10/10 ( Assertions )

#### Failed

    prggmrUnit v0.1.0

    ...FFF.......

    ----------------------------------
    Failures Detected

    ----------------------------------
    Test ( true ) had ( 1 ) failures
    1. Failed asserting  equals true

    ----------------------------------
    Test ( exception ) had ( 2 ) failures
    1. Exception not thrown
    2. Exception InvalidArgumentException was thrown expected Exception



    Ran 12 tests in 2 seconds and used 1.5 MB

    FAIL (2)
    Assertions 10/13
