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
 * The test ia a prggmr event, assertion calls are sent to the engine. It
 * supports the ability to skip a group or all of a test.
 */
class Test extends \prggmr\Event {
    
    /**
     * Test result states.
     */
    const FAIL = 0x04;
    const PASS = 0x05;
    const SKIP = 0x06;
    
    /**
     * Number of assertions run
     *
     * @var  integer
     */
    protected $_assertionCount = 0;

    /**
     * Number of passed assertions
     *
     * @var  integer
     */
    protected $_assertionPass = 0;

    /**
     * Number of failed assertions
     *
     * @var  integer
     */
    protected $_assertionFail = 0;
    
    /**
     * Number of skipped assertions
     *
     * @var  integer
     */
    protected $_assertionSkip = 0;
    
    /**
     * Passed assertions.
     *
     * @var  array
     */
    protected $_passedAssertions = array();
    
    /**
     * Failed assertions.
     *
     * @var  array
     */
    protected $_failedAssertions = array();
    
    /**
     * Reference to the engine running this test.
     *
     * @var  object
     */
    protected $_engine = null;
    
    /**
     * Constructs a new test event.
     *
     * @param  object  $engine  Engine
     */
    public function __construct($engine)
    {
        if (!$engine instanceof Engine) {
            throw new \InvalidArgumentException(
                'A valid engine object is required.'
            );
        }
        $this->_engine = $engine;
        // by default all tests are passed
        $this->setState(self::PASS);
    }
    
    /**
     * Sets to skip a portion or all of the test.
     *
     * @return void
     */
    public function skip()
    {
        if ($this->getState() !== self::FAIL) {
            $this->setState(self::SKIP);
        }
    }
    
    /**
     * Begins test again if set to skip.
     *
     * @return  void
     */
    public function endSkip()
    {
        if ($this->getState() !== self::FAIL) {
            $this->setState(self::PASS);
        }
    }
    
    /**
     * Calls an assertion.
     */
    public function __call($name, $args = null)
    {
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
        $result = $this->_engine->assert($name, $args);
        if ($result !== true) {
            $this->_failedAssertions[] = $name;
            $this->_engine->fire(Events::TEST_ASSERTION_FAIL, array($this, $result));
            $this->_assertionFail++;
            // if fail add the backtrace to state message
            $backtrace = debug_backtrace();
            $this->setState(self::FAIL, array($result, $backtrace));
        } else {
            $this->_passedAssertions[] = $name;
            $this->_engine->fire(Events::TEST_ASSERTION_PASS);
            $this->_assertionPass++;
        }
    }

    /**
     * Returns a count of the total assertions.
     *
     * @return  integer
     */
    public function assertionCount()
    {
        return $this->_assertionCount;
    }

    /**
     * Returns a count of passed assertions.
     *
     * @return  integer
     */
    public function failedAssertions()
    {
        return $this->_assertionFail;
    }

    /**
     * Returns a count of passed assertions.
     *
     * @return  integer
     */
    public function passedAssertions()
    {
        return $this->_assertionPass;
    }
    
    /**
     * Returns a count of skipped assertions.
     *
     * @return  integer
     */
    public function skippedAssertions()
    {
        return $this->_assertionSkip;
    }
    
    /**
     * Returns array of passed assertion tests.
     *
     * @return  array
     */
    public function getPassedAssertions()
    {
        return $this->_passedAssertions;
    }
    
    /**
     * Returns array of failed assertion tests.
     *
     * @return  array
     */
    public function getFailedAssertions()
    {
        return $this->_passedAssertions;
    }
}