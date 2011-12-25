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
 * PHPUnit Emulation
 */
prggmrunit::instance()->subscribe(function($event, $argv){
    
    /**
     * PHPUnit works as follows
     * 
     * phpunit Test
     * phpunit Test.php
     * phpunit Test Test.php
     *
     * @see http://www.phpunit.de/manual/3.5/en/textui.html
     */
    $c = count($argv);
    $argv = array_merge($argv);
    for ($i=0;$i!=$c;$i++) {
        $file = null;
        $class = null;
        
        // phpunit Test.php
        if (strpos($argv[$i], '.') !== false) {
            $file = $argv[$i];
            $explode = explode(DIRECTORY_SEPARATOR, $file);
            $class = substr($explode[count($explode)-1], 0, strpos($explode[count($explode)-1], '.'));
        }
        
        // phpunit Test Test.php
        if (isset($argv[$i+1]) && $argv[$i].'.php' == $argv[$i+1]) {
            $class = $argv[$i];
            $i++;
            $file = $argv[$i];
        }
        
        // phpunit Test
        if (null === $file) {
            $file = $argv[$i].'.php';
            $class = $argv[$i];
        }
        
        if (file_exists($file)) {
            require_once $file;
        } else {
            \prggmrunit\Output::send(sprintf(
                "Invalid test file %s%s",
                $file, PHP_EOL
            ), \prggmrunit\Output::ERROR);
        }
        
        if (class_exists($class)) {
            $test = new $class();
            if (!$test instanceof \PHPUnit_Framework_TestCase) {
                if (method_exists($test, 'suite')) {
                    $test->suite();
                } else {
                    \prggmrunit\Output::send(sprintf(
                        "Invalid test class %s, test class does not implement a suite method.%s",
                        \prggmrunit\Output::variable($test), PHP_EOL
                    ), \prggmrunit\Output::ERROR);
                }
            }
        } else {
            \prggmrunit\Output::send(sprintf(
                "Invalid test class %s%s",
                $class, PHP_EOL
            ), \prggmrunit\Output::ERROR);
        }
    }
    
}, \prggmrunit\Events::EMULATION_LOAD, 'PHPUnit Emulation Loader');


// a test case
class PHPUnit_Framework_TestCase extends \prggmrunit\emulator\Test {
    
    /**
     * Framework this test emulates.
     * 
     * @var  mixed
     */
    protected $_framework = \prggmrunit\Emulator::PHPUNIT;
    
    /**
     * Constructs a new emulation test for phpunit.
     */ 
    public function __construct($name = null, $data = null)
    {
        parent::__construct(prggmrunit::instance()->getAssertions());
        $ref = new \ReflectionClass($this);
        // hopefully php fixes this soon
        $class = $this;
        suite(function($suite) use ($class, $ref){
            if (method_exists($class, 'tearDown')) {
                $suite->tearDown(array($class, 'tearDown'));
            }
            if (method_exists($class, 'setUp')) {
                $suite->setUp(array($class, 'setUp'));
            }
            foreach ($ref->getMethods() as $_method) {
                test(array($class, $_method->getName()), $_method->getName(), 1, $class);
            }
        }, $ref->getName());
    }
}

// a test suite
class PHPUnit_Framework_TestSuite {
    
    /**
     * Test Suite
     */
    public function addTestSuite($test)
    {
        if (is_string($test)) {
            if (class_exists($test)) {
                $test = new $test();
            } else {
                \prggmrunit\Output::send(sprintf(
                    "Invalid test class %s%s",
                    $class, PHP_EOL
                ), \prggmrunit\Output::ERROR);
            }
        }
        
        if (!is_object($test)) {
            \prggmrunit\Output::send(
                "addTestSuite expects string or object",
                \prggmrunit\Output::ERROR
            );
        }
    }
    
}

/**
 * Something to note about phpunit emulation ...
 * 
 * PHPUnit does alot of type checking that isn't 100% nessecary
 * these are unit tests, they should test that a = b and leave the
 * rest to the user, but with that the emulation should be 100%
 */


/**
 * Used to return either the message given or default failure.
 *
 * @param  string  $default  The default.
 * @param  string  $message  The message.
 *
 * @return  string
 */
function prgut_pms($default = '', $message = null)
{
    return (null === $message) ?
        $default :
        $message;
}

/**
 * Begin emulators
 */

/***
 * Something to note about this code.
 *
 * This is an emulator, to me the code shoudn't need to much description
 * other than what it's currently doing ... since that is the most important
 * part ... not variables and names you already know about.
 */

/**
 * assertArrayHasKey
 */
\prggmrunit\Emulator::assertion(function($test, $key, array $array, $message = null){
    if (!isset($array[$key])) {
        prgut_pms(sprintf(
            'array %s doesn\'t have key %s',
            print_r($array, true),
            $key
        ), $message);
    }
    return true;
}, 'assertArrayHasKey');


/**
 * assertArrayNotHasKey
 */
\prggmrunit\Emulator::assertion(function($test, $key, array $array, $message = null){
    if (!isset($array[$key])) {
        return true;   
    }
    return prgut_pms(sprintf(
        'array %s has key %s',
        print_r($array, true),
        $key
    ), $message);
}, 'assertArrayNotHasKey');


/**
 * assertContains
 */
\prggmrunit\Emulator::assertion(function($test, $needle, $haystack, $message = null, $case = false, $objId = false){
    
    if (is_object($haystack)) {
        if ($haystack instanceof \Transversable) {
            if ($haystack instanceof \SplObjectStorage) {
                if (is_object($needle)) {
                    if ($haystack->contains($needle)) {
                        return true;
                    }
                    return prgut_pms(sprintf(
                        'Object %s does not contain %s',
                        get_class($haystack),
                        get_class($needle)
                    ), $message);
                } else {
                    // splobjectstorage store only objects
                    return prgut_pms(
                        'SplObjectStorage expects object',
                        $message
                    );
                }
            }
            $transverse = true;
        } else {
            return prgut_pms(
                'Non Transversable haystack',
                $message
            );
        }
    } elseif (is_array($haystack)) {
        $transverse = true;
    }
    
    if (isset($transverse)) {
        if (is_object($needle)) {
            foreach ($haystack as $_needle) {
                if (($objId && $_needle === $haystack) ||
                    (!$objId && $_needle == $haystack)) {
                    return true;
                }
            }
        } else {
            foreach ($haystack as $_needle) {
                if ($_needle == $haystack) {
                    return true;
                }
            }
        }

		return prgut_pms(sprintf(
			'%s does not contain %s',
			$haystack,
			$needle
		), $message);
    }
    
    if (is_string($needle)) {
        if (false === $case) {
            if (stripos($haystack, $needle) !== false) {
                return prgut_pms(sprintf(
                    'String %s does not contain %s (insensitive)',
                    $haystack, $needle
                ), $message);
            }
        } else {
            if (strpos($haystack, $needle) !== false) {
                return prgut_pms(sprintf(
                    'String %s does not contain %s (sensitive)',
                    $haystack, $needle
                ), $message);
            }
        }
    }
    
    return 'String, Array, Transversable or SplObjectStorage required';
}, 'assertContains');

/**
 * assertInstanceof
 *
 * I dont agree that the expected needs to be a string ... just to note
 */
\prggmrunit\Emulator::assertion(function($test, $expected, $actual, $message = '') {
    
    if (is_string($expected)) {
        if (class_exists($expected) || interface_exists($expected)) {
            if ($actual instanceof $expected) {
                return true;
            }
            return prgut_pms(sprintf(
                "%s is not an instance of %s",
                \prggmrunit\Output::variable($actual),
                $expected
            ));
        } else {
            return prgut_pms(sprintf(
                "Object %s could not be found",
                $expected
            ));
        }
    }
    
    return prgut_pms(
        "String name required"
    );
}, 'assertInstanceOf');

/**
 * assertEquals
 *
 * So the assertEquals ... if you have taken the time to look through
 * the almost insane amount of code and objects that are used to compare
 * values then reading this is will explain why I did it in a considerably
 * shorter version.
 *
 * phpunit provides a very extensive array of comparisons which is impressive
 * but realisitically over bloated, php itself provides excellent methods of
 * comparing variables within itself as expected.
 *
 * With all of that said, I am still supporting the emulation 100% so I must
 * write code that will be compatiable.
 */

/**
 * These are the assertions, they are written outside of the assertion
 * to save on memory, I am taking the approach of an array setup.
 *
 * TODO: Add object,exception,mockobject ( which is ? )
 */
$equalAssertions = array(
    'type' => array(
        'accept' => function() {
            return true;
        },
        'assert' => function($test, $expect, $actual, $message = null) {
            return gettype($expect) ===  gettype($actual);
        },
    ),
    'scalar' => array(
        'accept' => function($expect, $actual) {
            if ((is_scalar($expect) xor null === $expect) &&
                (is_scalar($actual) xor null === $expect)) {
                return true;
            }
            if ((is_string($expect) && is_object($actual) &&
                method_exists($actual, '__toString')) ||
                (is_string($actual) && is_object($expect) &&
                method_exists($expect, '__toString'))) {
                return true;
            }
            return false;
        },
        'assert' => function($test, $expect, $actual, $message = null, $delta = 0,
            $maxdepth = 10, $canonicalize = false, $ignorecase = false) {
            $expect = (string) $expect;
            $actual = (string) $actual;
            if ($ignorecase) {
                $expect = strtolower($expect);
                $actual = strtolower($actual);
            }
            return $expect === $actual;
        }
    ),
    'double' => array(
        'accept' => function($expect, $actual) {
            return (is_numeric($expect) && is_numeric($actual));
        },
        'assert' => function($test, $expect, $actual, $message = null, $delta = 0,
            $maxdepth = 10, $canonicalize = false, $ignorecase = false) {
            if (is_infinite($expect) || is_infinite($actual)) {
                return "Cannot compare infinite values ... or can we";
            }
            if ($delta === 0) {
                $delta = 0.0000000001;
            }
            if (abs($actual - $expect)) {
                return false;
            }
            return true;
        }
    ),
    'array' => array(
        'accept' => function($expect, $actual) {
            return is_array($expect) && is_array($actual);
        },
        'assert' => function($test, $expect, $actual, $delta = 0, $canonicalize = false,
            $case = false, &$processed = array()) {
            if ($canonicalize) {
                sort($expect);
                sort($actual);
            }
            $pass = true;
            /**
             * phpunit compares arrays by basically looping through the entire
             * array and checking each and every value.
             */
            foreach ($expect as $_key => $_value) {
                $pass = $test->assertEquals(
                    $_value, $actual[$_key], $delta, $canonicalize, $case, $processed
                );
            }
            return $pass;
        }
    ),
    'domdocument' => array(
        'accept' => function($expect, $actual) {
            return $expect instanceof \DOMDocument && $actual instanceof \DOMDocument;
        },
        'assert' => function($test, $expect, $actual) {
            return $expect->C14N() === $actual->C14N();
        }
    ),
    'resource' => array(
        'accept' => function($expect, $actual) {
            return is_resource($expect) && is_resource($actual);
        },
        'assert' => function($test, $expect, $actual) {
            return $expect == $actual;
        }
    ),
    'splobjectstorage' => array(
        'accept' => function($expect, $actual) {
            return $expect instanceof \SplObjectStorage && $actual instanceof \SplObjectStorage;
        },
        'assert' => function($test, $expect, $actual) {
            foreach ($expect as $_item) {
                if (!$actual->contains($_item)) return false;
            }
            foreach ($actual as $_item) {
                if (!$expect->contains($_item)) return false;
            }
        }
    ),
);

\prggmrunit\Emulator::assertion(function($test, $expect, $actual, $message = '',
    $delta = 0, $depth = 10, $canonicalize = false, $ignorecase = false) use ($equalAssertions)
{
    // everything passes by default >:)
    $pass = true;
    
    foreach ($equalAssertions as $_type => $_func) {
        if ($_func['accept']($expect, $actual)) {
            $pass = $_func['assert'](
                $test, $expect, $actual, $delta, $depth, $canonicalize, $ignorecase
            );
        }
    }
    
    // it failed for some reason
    if ($pass !== true) {
        return prgut_pms(
            (!is_string($pass)) ? sprintf(
                '% does not equal %s',
                \prggmrunit\Output::variable($expect),
                \prggmrunit\Output::variable($actual)
            ) : $pass,
            $message
        );
    }
    
    return true;
    
}, 'assertEquals');

\prggmrunit\Emulator::assertion(function($test, $var, $message = ''){
    if ($var === true) {
        return true;
    }
    
    return prgut_pms(sprintf(
        "%s is not true",
        \prggmrunit\Output::variable($var)
    ), $message);
}, 'assertTrue');

\prggmrunit\Emulator::assertion(function($test, $var, $message = ''){
    if ($var === false) {
        return true;
    }
    
    return prgut_pms(sprintf(
        "%s is not false",
        \prggmrunit\Output::variable($var)
    ), $message);
}, 'assertFalse');

\prggmrunit\Emulator::assertion(function($test, $var, $message = ''){
    if ($var !== null) {
        return true;
    }
    
    return prgut_pms(sprintf(
        "%s is null",
        \prggmrunit\Output::variable($var)
    ), $message);
}, 'assertNotNull');

\prggmrunit\Emulator::assertion(function($test, $var, $message = ''){
    if ($var === null) {
        return true;
    }
    
    return prgut_pms(sprintf(
        "%s is not null",
        \prggmrunit\Output::variable($var)
    ), $message);
}, 'assertNull');
