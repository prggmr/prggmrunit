<?php
namespace prggmrunit;
/**
 *  Copyright 2010-12 Nickolas Whiting
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
 * @copyright  Copyright (c), 2010-12 Nickolas Whiting
 */

/**
 * Generates output for a unit test.
 *
 * The object itself is only an interface to call a generator object,
 * the default is CLI.
 */
class Output implements Output_Generator {
    
    /**
     * Output generator
     *
     * @var  object
     */
    protected static $_generator = null;
    
    /**
     * Default generator used.
     *
     * @var string
     */
    protected static $_default = 'cli';
    
    /**
     * Flag to use output buffering.
     *
     * @var  boolean
     */
    protected static $_outputbuffer = false;
    
    /**
     * Output short version of variables.
     *
     * @var  boolean
     */
    protected static $_shortvars = true;
    
    /**
     * Maximum transverse depth.
     *
     * @var  int
     */
    protected static $_maxdepth = 2;
    
    /**
     * Output message types.
     */
    const MESSAGE = 0xF00;
    const ERROR   = 0xF01;
    const DEBUG   = 0xF02;
    const SYSTEM  = 0xF03;
    
    /**
     * Initalizes output.
     *
     * @param  string  $generator  Output generation object
     * @param  boolean  $buffer  use output buffer
     *
     * @return   void
     */
    public static function initalize(Output_Generator $generator = null, $buffer = false)
    {
        if (null === $generator) {
            $generator = static::$_default;
        }
        if (is_bool($buffer)) {
            ob_start();
            static::$_outputbuffer = $buffer;
            \prggmrunit::instance()->subscribe(\prggmrunit\Events::END, function(){
                ob_end_flush();
            }, 'Output buffer shutoff', 1000);
        }
        if (is_string($generator)) {
            // first startup
            $file = sprintf(
                '%s/output/%s.php',
                dirname(realpath(__FILE__)),
                $generator
            );
            // attempt to load
            if (file_exists($file)) {
                require_once $file;
            } else {
                throw new \Exception(
                    'Could not load default output, output generation simplified'
                );
            }
            static::$_generator = new \prggmrunit\Output\CLI();
        } else {
            if ($generator instanceof Output_Generator) {
                static::$_generator = $generator;
            }
        }
        
        // check for shortvars
        if (defined('PRGGMRUNIT_SHORTVARS')) {
            static::$_shortvars = PRGGMRUNIT_SHORTVARS;   
        }
        
        // check for transverse depth
        if (defined('PRGGMRUNIT_MAXVARDEPTH')) {
            static::$_maxdepth = PRGGMRUNIT_MAXVARDEPTH;   
        }
    }
    
    /**
     * Returns if short vars are enabled or to use.
     *
     * @param  string  $str 
     *
     * @return  boolean
     */
    public static function useShortVar($str = null)
    {
        return (null === $str) ? static::$_shortvars :
                (static::$_shortvars && is_string($str) && strlen($str) >= 60);
    }
    
    /**
     * Sends a string to output.
     *
     * @param  string  $string
     * @param  string  $type  
     *
     * @return  void
     */
    public static function send($string, $type = null)
    {
        if (null === static::$_generator) {
            static::initalize();
        }
        if (null === $type) {
            $type = Output::MESSAGE;
        }
        static::$_generator->send($string, $type);
    }
    
    /**
     * Generates PHP vars like printr, var_dump the output is limited
     * by using shortvars and the maximum output length.
     *
     * Recursion is not checked for.
     *
     * @param  mixed  $v
     * @param  integer  $depth  Current transvering depth.
     *
     * @return  string  
     */
    public static function variable($v, &$depth = 0)
    {
        switch ($v) {
            case is_bool($v):
                if ($v) {
                    return "bool(true)";
                }
                return "bool(false)";
                break;
            case is_null($v):
                if (false === $v) {
                    return "bool(false)";
                }
                return "null";
                break;
            case is_int($v):
            case is_float($v):
            case is_double($v):
            default:
                return sprintf('%s(%s)',
                    gettype($v),
                    $v);
                break;
            case is_string($v):
                return sprintf('string(%s)',
                    (static::useShortVar($v)) ? substr($v, 0, 60) : $v
                );
                break;
            case is_array($v):
                $r = array();
                foreach ($v as $_key => $_var) {
                    if ($depth >= static::$_maxdepth) break;
                    $depth++;
                    $r[] = sprintf('[%s] => %s',
                        $_key,
                        static::variable($_var, $depth)
                    );
                }
                $return = sprintf('array(%s)', implode(", ", $r));
                return (static::useShortVar($return)) ? sprintf('%s...)',
                    substr($return, 0, 60)) : $return;
                break;
            case is_object($v):
                return sprintf('object(%s)', get_class($v));
            break;
        }
        
        return "unknown";
    }
    
    /**
     * Outputs a readable backtrace, by default it just dumps it from a for.
     * The output generator is at fault for providing it simplified.
     *
     * @param  array  $backtrace  debug_print_backtrace()
     *
     * @return  void
     */
    public static function backtrace($backtrace)
    {
        $endtrace = '';
        for($a=0;$a!=count($backtrace);$a++) {
            if (isset($backtrace[$a]['file']) && isset($backtrace[$a]['line'])) {
                $endtrace .= sprintf("{%s} - %s %s %s\n",
                    $a,
                    $backtrace[$a]['file'],
                    $backtrace[$a]['line'],
                    $backtrace[$a]['function']
                );
            }
        }
        static::send($endtrace, static::ERROR);
    }
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
    public static function send($string, $type = null);
    
    /**
     * Generates readable PHP vars.
     *
     * @param  mixed  $var
     *
     * @return  string  
     */
    public static function variable($v);
    
    /**
     * Outputs a test failure.
     *
     */
}
