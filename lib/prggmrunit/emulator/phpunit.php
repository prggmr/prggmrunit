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
once(\prggmrunit\Events::EMULATION_LOAD, function($event, $argv){
    
    /**
     * PHPUnit works as follows
     * 
     * phpunit Test
     * phpunit Test.php
     * phpunit Test Test.php
     *
     * @http://www.phpunit.de/manual/3.0/en/textui.html
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
        if ($argv[$i].'.php' == $argv[$i+1]) {
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
            } else {
                $test = new Test();
            }
        } else {
            \prggmrunit\Output::send(sprintf(
                "Invalid test class %s%s",
                $class, PHP_EOL
            ), \prggmrunit\Output::ERROR);
        }
    }
    
}, 'PHPUnit Emulation Loader');


// a test case
class PHPUnit_Framework_TestCase extends \prggmrunit\Test {
    
    /**
     * Constructs a new emulation test for phpunit
     */ 
    public function __construct($name = null, $data = null)
    {
        parent::__construct(Prggmrunit::instance());
        $ref = new \ReflectionClass($this);
        // hopefully php fixes this soon
        $class = $this;
        suite(function($suite) use ($class, $ref){
            if (method_exists($class, 'tearDown')) {
                $suite->tearDown(array($class, 'tearDown'));
            }
            if (method_exists($class, 'setUp')) {
                $suite->tearDown(array($class, 'setUp'));
            }
            foreach ($ref->getMethods() as $_method) {
                test(new \prggmr\Subscription(array($class, $_method->getName())));
            }
        }, $name);
        
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
\prggmrunit\Emulator::assertion(function($key, array $array, $message = null){
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
\prggmrunit\Emulator::assertion(function($key, array $array, $message = null){
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
\prggmrunit\Emulator::assertion(function($needle, $haystack, $message = null, $case = false, $objId = false){
    
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
* Asserts that a variable is of a given type.
*
* @param string $expected
* @param mixed  $actual
* @param string $message
* @since Method available since Release 3.5.0
*/
\prggmrunit\Emulator::assertion(function($expected, $actual, $message = '') {
   if (is_string($expected)) {
       if (class_exists($expected) || interface_exists($expected)) {
           $constraint = new PHPUnit_Framework_Constraint_IsInstanceOf(
             $expected
           );
       }

       else {
           throw PHPUnit_Util_InvalidArgumentHelper::factory(
             1, 'class or interface name'
           );
       }
   } else {
       throw PHPUnit_Util_InvalidArgumentHelper::factory(1, 'string');
   }

   self::assertThat($actual, $constraint, $message);
}, 'assertInstanceOf');
