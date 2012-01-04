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
 * Assertion tests
 */
class Assertions {

    /**
     * Default assertion namespace
     */
     const PRGGMRUNIT = 0x00001;

    /**
     * Array of avaliable assertion tests.
     *
     * @var  array
     */
    protected $_assertions = array();
    
    /**
     * Adds a new assertion test to run within a unit test.
     *
     * @param  object  $closure  Callable php function
     * @param  string  $name  Test name
     * @param  integer $namespace  Namespace this assertions belongs
     *
     * @throws  InvalidArgumentException
     * 
     * @return  void
     */
    public function assertion($closure, $name, $namespace = null)
    {
        if (null === $namespace) {
            $namespace = self::PRGGMRUNIT;
        }   
        if (!is_string($name)) {
            throw new \InvalidArgumentException(
                'assertion name must be a string'
            );
        }
        if (!isset($this->_assertions[$namespace])) {
            $this->_assertions[$namespace] = array();
        }
        $this->_assertions[$namespace][$name] = $closure;
    }
    
    /**
     * Runs an assertion.
     *
     * @param  string  $assertion  Assertion test
     * @param  array  $params  Parameters
     * @param  integer $namespace  Namespace this assertions belongs
     * 
     * @return  boolean
     */
    public function assert($name, $params, $namespace = null)
    {
        if (null === $namespace) {
            $namespace = self::PRGGMRUNIT;
        } 
        if (isset($this->_assertions[$namespace][$name])) {
            return call_user_func_array($this->_assertions[$namespace][$name], $params);
        } else {
            return null;
        }
    }    
    
    
    /**
     * Returns the currently avaliable assertions.
     *
     * @return  array
     */
    public function getAssertions($namespace = null)
    {
        if (null === $namespace) {
            $namespace = self::PRGGMRUNIT;
        }
        return array_keys($this->_assertions[$namespace]);
    }
}