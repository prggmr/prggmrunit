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
 * Default assertions
 *
 * The following assertions are the default avaliable assertions, they 
 * are automatically registered in the prggmrunit global scope.
 */

/**
 * Equals assertion.
 *
 * Asserts that two values are exactly equal.
 */
assertion(function($test, $expect, $actual){
    if ($expect === $actual) return true;
    return sprintf(
        "%s does not equal %s",
        \prggmrunit\Output::variable($actual, true),
        \prggmrunit\Output::variable($expect, true)
    );
}, 'equal');

/**
 * Event assertion.
 *
 * Asserts that the getData result associated after the event fire
 * equals what is contained in the resulting event.
 */
assertion(function($test, $signal, $expected, $params = null, $event = null, $engine = null){
    if (null !== $engine) {
        $fire = $engine->fire($signal, $params, $event);
    } else {
        $fire = \prggmr::instance()->fire($signal, $params, $event);
    }
    if ($fire->getData() === $expected) return true;
    
    return sprintf(
        "Event data %s does not equal %s",
        \prggmrunit\Output::variable($fire->getData()),
        \prggmrunit\Output::variable($expected)
    );
}, 'event');

/**
 * Exception assertion.
 *
 * Asserts that the giving code throws the giving Exception.
 */
assertion(function($test, $exception, $code){
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
 * True assertion.
 *
 * Asserts the provided expression results to true.
 */
assertion(function($test, $var){
    if ($var === true) return true;
    return sprintf(
        '%s does not equal true',
        \prggmrunit\Output::variable($var)
    );
}, 'true');

/**
 * False assertion.
 *
 * Asserts the provided expressions results to false.
 */
assertion(function($test, $var){
    if ($var === false) return true;
    return sprintf(
        '%s does not equal false',
        \prggmrunit\Output::variable($var)
    );
}, 'false');

/**
 * Null assertion
 *
 * Asserts the given expression results to null.
 */
assertion(function($test, $var){
    if ($var === null) return true;
    return sprintf(
        '%s equal null',
        \prggmrunit\Output::variable($var)
    );
}, 'null');

/**
 * Array assertion
 *
 * Asserts the given variable is an array.
 */
assertion(function($test, $array){
    if (is_array($array)) return true;
    return sprintf(
        '%s is not an array',
        \prggmrunit\Output::variable($array)
    );
}, 'array');

/**
 * String assertion
 *
 * Asserts the given variable is a string.
 */
assertion(function($test, $string){
    if (is_string($string)) return true;
    return sprintf(
        '%s is not a string',
        \prggmrunit\Output::variable($string)
    );
}, 'string');

/**
 * Integer assertion
 *
 * Asserts the given variable is a integer.
 */
assertion(function($test, $int){
    if (is_int($int)) return true;
    return sprintf(
        '%s is not an integer',
        \prggmrunit\Output::variable($int)
    );
}, 'integer');

/**
 * Float assertion
 *
 * Asserts the given variable is a float.
 */
assertion(function($test, $float){
    if(is_float($float)) return true;
    return sprintf(
        'Failed asserting %s is a float',
        \prggmrunit\Output::variable($float)
    );
}, 'float');

/**
 * Object assertion
 *
 * Asserts the given variable is an object.
 */
assertion(function($test, $object){
    if (is_object($object)) return true;
    return sprintf(
        '%s is not an object',
        \prggmrunit\Output::variable($object)
    );
}, 'object');

/**
 * Instanceof assertion
 *
 * Asserts the given object is an instance of the provided class.
 */
assertion(function($test, $object, $class){
    if (get_class($class) === $object) return true;
    return sprintf(
        '%s is not an instance of %s',
        \prggmrunit\Output::variable($class),
        \prggmrunit\Output::variable($object)
    );
}, 'instanceof');
