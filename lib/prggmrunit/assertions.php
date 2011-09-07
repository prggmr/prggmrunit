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
 * Default assertions file.
 */
$prggmrunit = Prggmrunit::instance();

/**
 * equals test
 */
$prggmrunit->assertion(function($expect, $actual){
    if ($expect === $actual) return true;
    return sprintf(
        "%s does not equal %s",
        print_r($actual, true),
        print_r($expect, true)
    );
}, 'equal');

/**
 * event test
 */
$prggmrunit->assertion(function($signal, $expected, $params = null, $event = null, $engine = null){
    if (null !== $engine) {
        $fire = $engine->fire($signal, $params, $event);
    } else {
        $fire = \Prggmr::instance()->fire($signal, $params, $event);
    }
    if ($fire->getData() === $expected) return true;
    
    return sprintf(
        "Event data %s does not equal %s",
        print_r($fire->getData(), true),
        print_r($expected, true)
    );
}, 'event');

/**
 * exception test
 */
$prggmrunit->assertion(function($exception, $code){
    try {
        $code();
    } catch (\Exception $e) {
        if (get_class($e) !== $exception) return sprintf(
            'Exception %s was thrown expected %s',
            get_class($e),
            $exception
        );
        return true;
    }
    return 'Exception was not thrown';
}, 'exception');

/**
 * true
 */
$prggmrunit->assertion(function($var){
    if ($var === true) return true;
    return sprintf(
        '%s does not equal true',
        print_r($var, true)
    );
}, 'true');

/**
 * false
 */
$prggmrunit->assertion(function($var){
    if ($var === false) return true;
    return sprintf(
        '%s does not equal false',
        print_r($var, true)
    );
}, 'false');

/**
 * null
 */
$prggmrunit->assertion(function($var){
    if ($var === null) return true;
    return sprintf(
        '%s equal null',
        print_r($var, true)
    );
}, 'null');

/**
 * array
 */
$prggmrunit->assertion(function($array){
    if (is_array($array)) return true;
    return sprintf(
        '%s is not an array',
        gettype($array)
    );
}, 'array');

/**
 * string
 */
$prggmrunit->assertion(function($string){
    if (is_string($string)) return true;
    return sprintf(
        '%s is not a string',
        gettype($string)
    );
}, 'string');

/**
 * integer
 */
$prggmrunit->assertion(function($int){
    if (is_int($int)) return true;
    return sprintf(
        '%s is not an integer',
        gettype($int)
    );
}, 'integer');

/**
 * float
 */
$prggmrunit->assertion(function($float){
    if(is_float($float)) return true;
    return sprintf(
        'Failed asserting %s is a float',
        gettype($float)
    );
}, 'float');

/**
 * object
 */
$prggmrunit->assertion(function($object){
    if (is_object($object)) return true;
    return sprintf(
        '%s is not an object',
        gettype($object)
    );
}, 'object');

/**
 * instanceof
 */
$prggmrunit->assertion(function($object, $class){
    if (get_class($class) === $object) return true;
    return sprintf(
        '%s is not an instance of %s',
        get_class($class),
        $object
    );
}, 'instanceof');