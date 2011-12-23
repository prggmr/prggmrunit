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
final class Emulator {
    
    /**
     * Emulators definitions
     */ 
    const PHPUNIT = 'phpunit';
    
    /**
     * Avaliable emulators.
     *
     * @var  array
     */
    protected static $_emulators = array(
        self::PHPUNIT => self::PHPUNIT
    );
    
    /**
     * Current emulators running.
     *
     * @var  array
     */
    protected static $_activated = array();
    
    /**
     * Assertion object
     *
     * @var  object
     */
    protected static $_assertions = null;
    
    /**
     * no construction
     */
    private function __construct(){}
    
    /**
     * Loads an emulator.
     *
     * @param  string  $framework  Framework to emulate.
     * @param  array  $argv  Test arguments array
     * @param  object  $assertions  \prggmrunit\Assertions
     * 
     * @return  boolean
     */
    public static function emulate($framework, $argv, $assertions)
    {
        if (isset(static::$_emulators[$framework])) {
            
            static::$_activated[] = $framework;
            
            if (null === $assertions || !is_object($assertions)) {
                $assertions = new Assertions();
            } elseif (!$assertions instanceof \prggmrunit\Assertions) {
                throw new \InvalidArgumentException(sprintf(
                    'Expected instance of \prggmrunit\Assertions received %s',
                    get_class($assertions)
                ));
            }
            
            static::$_assertions = $assertions;

            require_once sprintf(
                'emulator/%s.php',
                $framework
            );
            
            // tell the system to load the emulator
            \prggmrunit::instance()->fire(\prggmrunit\Events::EMULATION_LOAD, array($argv));
            
            if (defined('PRGGMRUNIT_EMULATION_DEBUG')) {
                
                // Turn off assertion outputs
                
                // Assertion Pass
                \prggmrunit::instance()->subscribe(\prggmrunit\Events::TEST_ASSERTION_PASS, function($event){
                    $event->halt();
                }, 'Debugging Halt Pas', 1);
        
                // Assertion Fail
                \prggmrunit::instance()->subscribe(\prggmrunit\Events::TEST_ASSERTION_FAIL, function($event){
                    $event->halt();
                }, 'Debugging Halt Fail', 1);
                
                // Assertion Skip
                \prggmrunit::instance()->subscribe(\prggmrunit\Events::TEST_ASSERTION_SKIP, function($event){
                    $event->halt();
                }, 'Debugging Halt Skip', 1);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns currently activated emulators.
     *
     * @return  array
     */
    public static function activated(/* ... */)
    {
        return static::$_activated;
    }
    
    /**
     * Creates a new emulation assertion.
     *
     * @param  object  $closure  Callable php function
     * @param  string  $name  Test name
     * @param  mixed  $namespace  Emulation namespace
     *
     * @return  void
     */
    public static function assertion($closure, $name, $namespace = null)
    {
        if (null === $namespace) {
            // use the last activated emulator
            $namespace = end(static::$_activated);
        }

        static::$_assertions->assertion($closure, $name, $namespace);
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
