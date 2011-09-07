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
class PHPUnit_Framework_TestCase {
    
    /**
     * Constructs a new emulation test for phpunit.
     *
     * 
     */ 
    public function __construct($name, $data)
    {
        $class = new \ReflectionClass($this);
        var_dump($class->getMethods());
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
\prggmrunit\Emulator::assertion(function($needle, $haystack, $message = null, $case = false){
    
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
                if ($_needle === $haystack) {
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