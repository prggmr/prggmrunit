# prggmrunit

lightweight event driven unit testing library for PHP 5.3+ applications

## Introduction

prggmrunit is a unit testing library built using the prggmr event library, it
is designed to be incredibly simple, intuitive and lighting fast. prggmrunit was
developed from the hassle of the currently avaliable testing suites being oversized,
problamatic installations and unituitive API's.

## Install

This is the only current method of installation an installation script will be written soon enough.

    cd "location for your install"
    git clone git@github.com:prggmrlabs/prggmrunit.git
    chmod +x prggmrunit
    sudo ln -s prggmrunit /usr/local/bin/

## Usage

prggmrunit contains a single API function.

    test($name, $test);

### Writing Tests

Writing a unit tests is as follows:

    // creates a new tests
    // the "$test" parameter is the testing suite we use for assertions
    test('mytestname', function($test){
        $test->equals('a', 'a');
    });

    // and another example
    test('anothertest', function($test){
        $test->true((true == 1));
        $test->false(true);
    });

### Assertions

prggmrunit provides the following methods for assertions in your unit tests, each
method below is callable using the "$test" parameter in your tests.

#### equals($expected, $actual)

Asserts that $expected is strictly equal to $actual.

#### true($var)

Asserts the given variable or expressions equals true.

#### false($var)

Asserts the given variable or expressions equals false.

#### null($var)

Asserts the given variable or expressions equals null.

#### array($var)

Asserts the given variable is an array.

#### string($var)

Asserts the given variable is a string.

#### integer($var)

Asserts the given variable is an integer.

#### float($var)

Asserts the given variable is a float.

#### object($var)

Asserts the given variable is an object.

#### instanceof($var)

Asserts the given variable is an instance of a specific object.

#### event($signal, $params, $expected, $event = null, $engine = null)

Asserts the given event signal result data equals expected result

### Custom Assertions

You can also create custom assertions using the following code.

    $event = $GLOBALS['_PRGGMRUNIT_EVENT']

    $event->addTest(function($test){
        $test->test(true|false eval, 'failure message');
    }, 'nameofmethod');

    $GLOBALS['_PRGGMRUNIT_EVENT'] = $event;

#### Example

This will create an assertion to test for values greater than 100

    $event = $GLOBALS['_PRGGMRUNIT_EVENT'];

    $event->addTest(function($test, $value){
        //$test->test(false, 'Value is not an integer');
        $test->test(($value >= 100), sprintf(
            'Value %s is not greater than 100!',
            $value
        ));
    }, 'greater100');

And a test

    test('equals', function($test){
        $test->greater100(50);
        $test->greater100(100);
    });


### Output

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
