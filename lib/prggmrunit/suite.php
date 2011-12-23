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
 * A suite is a grouping of tests with few differences, with the major
 * difference of support for setUp and tearDown instructions support.
 *
 */
class Suite {
    
    /**
     * setUp function.
     *
     * @var  closure
     */
    protected $_setup = null;
    
    /**
     * tearDown function.
     *
     * @var  closure
     */
    protected $_teardown = null;
    
    /**
     * Test object used in the suite.
     *
     * @var  object  \prggmrunit\Test
     */
    protected $_test = null;
    
    /**
     * Creates a new testing suite.
     *
     * @param  closure  $suite
     * @param  object  $engine  \prggmrunit\Engine
     */
    public function __construct($test, $engine)
    {
        $this->_test = new Test($engine->getAssertions());
        $this->_test->setSuite();
        $engine->suite($this);
        call_user_func_array($test, array($this));
        $engine->suite();
    }
    
    /**
     * Registers a setUp function.
     *
     * @param  closure  
     */
    public function setup($function = null)
    {
        if (null === $function) {
            return $this->_setup;
        }
        $this->_setup = $function;
    }
    
    /**
     * Registers a tearDown function.
     *
     * @param  closure
     */
    public function teardown($function = null)
    {
        if (null === $function) {
            return $this->_teardown;
        }
        $this->_teardown = $function;
    }
    
    /**
     * Returns the test object associated with this suite.
     *
     * @return  object
     */
    public function test()
    {
        return $this->_test;
    }
    
}
