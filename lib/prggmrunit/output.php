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
 * Generates output for a unit test.
 *
 * The object itself is only an interface to call a generator object,
 * the default is CLI.
 */
class Output {
    
    /**
     * Output generator
     *
     * @var  object
     */
    protected $_generator = null;
    
    /**
     * Default generator used.
     *
     * @var string
     */
    protected $_default = 'CLI';
    
    /**
     * Flag to use output buffering.
     *
     * @var  boolean
     */
    protected $_outputbuffer = false;
    
    /**
     * Initalizes output.
     *
     * @param  string  $generator  Output generation object
     * @param  boolean  $buffer  use output buffer
     *
     * @return   void
     */
    public static function initalize(Output_Generator $generator, $buffer = false)
    {
        if (is_bool($buffer)) static::$_outputbuffer = $buffer;
        
        
    }
    
    /**
     * Sends a string to output.
     *
     * @param  string  $string
     */
    public static function send($string)
    {
        echo $_generator::send($string);
    }
    
    /**
     * 
     */
}

/**
 * Output Generator
 */
interface Output_Generator {

    /**
     * Sends a string to output.
     *
     * @param  string  $string
     */
    public static function send($string);
}