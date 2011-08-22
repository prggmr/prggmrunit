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
 * A suite is a grouping of tests with few differences, with the major
 * difference of support for setUp and tearDown functions.
 *
 */
class Suite {
    
    /**
     * setUp function.
     *
     * @var  closure
     */
    protected $_setUp = null;
    
    /**
     * tearDown function.
     *
     * @var  closure
     */
    protected $_tearDown = null;
    
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
     * @param  object  prggmrunit\Engine
     */
    public function __construct($test, $engine)
    {
        if (!$engine instanceof Engine) {
            throw new \InvalidArgumentException(
                'A valid engine object is required.'
            );
        }
        $this->_engine = $engine;
        $this->_test = new Test($engine);
        call_user_func_array($test, array($this));
    }
    
    /**
     * Registers a setUp function.
     *
     * @param  closure  
     */
    public function setUp($function)
    {
        $this->_setUp = $function;
    }
    
    /**
     * Registers a tearDown function.
     *
     * @param  closure
     */
    public function tearDown($function)
    {
        $this->tearDown = $function;
    }
    
    /**
     * Registers a new test in the suite.
     *
     * @param  closure  $function  Test function
     * @param  string  $name  Test name
     *
     * @return  void
     */
    public function test($test, $name = null, $repeat = 1)
    {
        $subscription = new \prggmr\Subscription($test, $name, $repeat);
        if ($this->_setUp != null) {
            $subscription->preFire($this->_setUp);
        }
        if ($this->tearDown != null) {
            $subscription->postFire($this->_setUp);
        }
        $this->_engine->test($subscription, $name, null, $this->_test);
        return $subscription;
    }
    
}