<?php
/**
 *  Copyright 2010 Nickolas Whiting
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
 * @author  Nickolas Whiting  <me@nwhiting.com>
 * @package  prggmrunit
 * @copyright  Copyright (c), 2011 Nickolas Whiting
 */

/**
 * PHPUnit Emulation
 */
class PHPUnit_Framework_TestCase extends \prggmrunit\Test {}

/**
 * Something to note about phpunit emulation ...
 * 
 * PHPUnit does alot of type checking that isn't 100% nessecary
 * these are unit tests, they should test that a = b and leave the
 * rest to the user, but with that the emulation should be 100%
 */


/**
 * Begin emulators
 */
Emulator::assertion(function($key, array $array, $message = null){
    if (!isset($array[$key])) {
        if (null === $message) {
            return sprintf(
                'array %s doesn\'t have key %s',
                print_r($array, true),
                $key
            );
        }
        return $message;
    }
    return true;
}, 'assertArrayHasKey');

Emulator::assertion(function($key, array $array, $message = null){
    if (!isset($array[$key])) {
        return true;   
    }
    if (null === $message) {
        return sprintf(
            'array %s has key %s',
            print_r($array, true),
            $key
        );
    }
    return $message;
}, 'assertArrayNotHasKey');

Emulator::assertion(function($needle, $haystack, $message = null, $case = false){
    
    if (is_object($haystack)) {
        if ($haystack instanceof \Transversable) {
            if ($haystack instanceof \SplObjectStorage) {
                if (is_object($needle)) {
                    if ($haystack->contains($needle)) {
                        return true;
                    }
                    return sprintf(
                        'Object %s does not contain %s',
                        get_class($haystack),
                        get_class($needle)
                    );
                } else {
                    // splobjectstorage store only objects
                    return 'SplObjectStorage expects object';
                }
            }
            $transverse = true;
        } else {
            return 'Non Transversable haystack';
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
        /**
         * LEFT OFF HERE return
         */
    }

    self::assertThat($haystack, $constraint, $message);
}, 'assertContains');