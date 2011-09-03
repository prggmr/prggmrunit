<?php
namespace prggmrunit;
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
     * 
     * @return  boolean
     */
    public static function emulate($framework)
    {
        if (isset(static::$_emulators[$framework])) {
            static::$_activated[] = $framework;
            require_once sprintf(
                'emulator/%s.php',
                $framework
            );
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
     * Creates a new emulation assertion, also ensures assertions are not added
     * twice.
     *
     * @param  object  $closure  Callable php function
     * @param  string  $name  Test name
     *
     * @return  void
     */
    public static function assertion($closure, $name)
    {
        if (!isset(static::$_assertions[$name])) {
            static::$_assertions[$name] = true;
            Prggmrunit::instance()->assertion($closure, $name);
        }
    }
}