<?php
namespace prggmrunit\emulator;
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
 * prggmrunit emulation test.
 *
 * The test itself is almost no different from a normal prggmrunit test
 * with the major difference being assertion handling, assertions are
 * sent to the Emulator rather than the Engine.
 * 
 */
class Test extends \prggmrunit\Test {
    
    /**
     * Framework this test emulates.
     * 
     * @var  mixed
     */
    protected $_framework = 0xAE00;
    
    /**
     * Returns the framework this test emulates.
     *
     * @return  mixed
     */
    public function getFramework()
    {
        return $this->_framework;
    }
    
    /**
     * Runtime test params.
     */
    protected $_runtimeParams = array();
    
    /**
     * Calls an assertion.
     */
    public function __call($name, $args = null)
    {
        
        /**
         * this has some duplicate code ... this needs to be addressed
         */
        
        $this->_assertionCount++;
        // skip if fail or skip state
        if ($this->getState() === self::FAIL ||
            $this->getState() === self::SKIP) {
            if ($this->getState() === self::SKIP) {
                $this->_engine->fire(Events::TEST_ASSERTION_SKIP);
            }
            $this->_assertionSkip++;
            return false;
        }
        try {
            $result = \prggmrunit\Emulator::assert(
                $name, $args, $this->getFramework()
            );
        } catch (\Exception $e) {
            $result = false;
        }
        if ($result !== true) {
            $this->_failedAssertions[] = $name;
            \Prggmrunit::instance()->fire(
                \prggmrunit\Events::TEST_ASSERTION_FAIL,
                array($this, $result)
            );
            $this->_assertionFail++;
            // if fail add the backtrace to state message
            $backtrace = debug_backtrace();
            $this->setState(self::FAIL, array($result, $backtrace));
            return false;
        } else {
            $this->_passedAssertions[] = $name;
            \Prggmrunit::instance()->fire(
                \prggmrunit\Events::TEST_ASSERTION_PASS
            );
            $this->_assertionPass++;
            return true;
        }
    }
}