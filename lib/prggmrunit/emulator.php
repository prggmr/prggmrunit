<?php
namespace prggmrunit;
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
 * Run tests emulation from supported test frameworks.
 *
 * The emulation works by masking itself directly as a given test case
 * for the given library, mirrors their assertion API and attempts to
 * gracefully degrade for unsupported features.
 */
class Emulator {
    
    /**
     * Emulators definitions
     */ 
    const PHPUNIT = 'phpunit';
    
    /**
     * Avaliable emulators.
     *
     * @var  array
     */
    private static $_emulators = array(
        self::PHPUNIT => self::PHPUNIT
    );
    
    /**
     * Current emulators running.
     *
     * @var  array
     */
    private static $_activated = array();
    
    /**
     * Running list of emulator assertions.
     *
     * @var  array
     */
    private static $_assertions = array();
    
    /**
     * no construction
     */
    private function __construct(){}
    
    /**
     * Loads an emulator.
     *
     * @param  string  $framework  Framework to emulate.
     * @param  array  $argv  Test arguments array
     * 
     * @return  boolean
     */
    public static function emulate($framework, $argv)
    {
        if (isset(static::$_emulators[$framework])) {
            static::$_activated[] = $framework;
            require_once sprintf(
                'emulator/%s.php',
                $framework
            );
            // tell the system to load the emulator
            fire(\prggmrunit\Events::EMULATION_LOAD, array($argv));
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns currently activated emulators.
     *
     * @return  array
     */
    public function activated(/* ... */)
    {
        return static::$_activated;
    }
    
    /**
     * Creates a new emulation assertion.
     *
     * @param  object  $closure  Callable php function
     * @param  string  $name  Test name
     * @param  mixed  $framework  Emulation framework
     *
     * @return  void
     */
    public static function assertion($closure, $name, $framework = null)
    {
        if (null === $framework) {
            // use the last activated emulator
            $framework = end(static::$_activated);
        }
        if (!isset(static::$_assertions[$framework])) {
            static::$_assertions[$framework] = array();
        }
        static::$_assertions[$framework][$name] = $closure;
    }
    
    /**
     * Runs an assertion.
     *
     * @param  string  $assertion  Assertion test
     * @param  array  $params  Parameters
     * @param  mixed  $framework  Emulation framework
     * 
     * @return  boolean
     */
    public static function assert($name, $params, $framework = null)
    {
        if (null === $framework) {
            $framework = end($framework);
        }
        $result = null;
        if (isset(static::$_assertions[$framework][$name])) {
            $result = call_user_func_array(
                static::$_assertions[$framework][$name], $params
            );
        }
        
        // fun
        if (defined('PRGGMRUNIT_EMULATION_DEBUG')) {
            if ($result !== true) {
                // everything fails here
                if ($result === null) {
                    Output::send(sprintf(
                        "Emulation framework %s assertion %s does not exist.%s",
                        Output::variable($framework), Output::variable($name), PHP_EOL
                    ), Output::DEBUG);
                    Output::send(sprintf(
                        "Avaliable emulation assertions %s%s%s",
                        PHP_EOL,
                        implode(PHP_EOL, array_keys(static::$_assertions[$framework])),
                        PHP_EOL
                    ), Output::DEBUG);
                } else {
                    Output::send(sprintf(
                        "Emulation Assertion %s::%s failed.%s",
                        $framework,
                        $name,
                        PHP_EOL,
                        PHP_EOL
                    ), Output::DEBUG);
                }
                Output::send(sprintf(
                    "Params%s%s%s",
                    PHP_EOL,
                    Output::variable($params),
                    PHP_EOL
                ), Output::DEBUG);
                Output::backtrace(debug_backtrace());
            }
        }
        
        return $result;
    }
    
    /**
     * Returns an array of emulator libraries currently avaliable.
     *
     * @return  array
     */
    public static function getEmulators()
    {
        return static::$_emulators;
    }
}