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
     * Skipped assertion tests.
     *
     * @var  array
     */
    protected $_skippedAssertions = array();
    
    /**
     * Reference to the engine running this test.
     *
     * @var  object
     */
    protected $_engine = null;

    /**
     * Reference to the suite this test belongs.
     *
     * @var  object|null
     */
    protected $_suite = null;

    /**
     * Result of the run test.
     *
     * @var  integer
     */
    protected $_testResult = null;

    /**
     * Messages associated with test run.
     *
     * @var  array
     */
    protected $_testMessages = array();
    
    /**
     * Constructs a new test event.
     *
     * @param  object  $engine  \prggmrunit\Engine
     */
    public function __construct($engine)
    {
        $this->_engine = $engine;
        // by default all tests are passed
        $this->setState(self::PASS);
    }
    
    /**
     * Sets to skip a portion or all of the test.
     *
     * @return void
     */
    public function skip(/* ... */)
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
    public function endSkip(/* ... */)
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
        if ($this->getTestResult() === self::FAIL ||
            $this->getTestResult() === self::SKIP) {
            if ($this->getTestResult() === self::SKIP) {
                $this->_engine->fire(Events::TEST_ASSERTION_SKIP);
            }
            $this->_skippedAssertions[] = $name;
            $this->_assertionSkip++;
            return false;
        }
        try {
            $result = $this->_engine->assert($name, $args);
        } catch (\Exception $e) {
            $result = false;
        }
        if ($result !== true) {
            $this->_failedAssertions[] = $name;
            $this->_engine->fire(Events::TEST_ASSERTION_FAIL, array($this, $result));
            $this->_assertionFail++;
            // if fail add the backtrace
            $backtrace = debug_backtrace();
            $this->setTestResult(self::FAIL);
            $this->addTestMessage($result, $backtrace, Output::ERROR);
            return false;
        } else {
            $this->_passedAssertions[] = $name;
            $this->_engine->fire(Events::TEST_ASSERTION_PASS);
            $this->_assertionPass++;
            return true;
        }
    }

    /**
     * Sets the test result.
     * 
     * @param  integer  $result
     *
     * @return  void
     */
    public function setTestResult($result) 
    {
        $this->_testResult = $result; 
    }

    /**
     * Returns the test result.
     *
     * @return  integer
     */
    public function getTestResult()
    {
        return $this->_testResult;
    }

    /**
     * Sets a message for the test.
     *
     * @param  string  $message
     * @param  mixed  $data
     * @param  integer  $type 
     * 
     * @return  void
     */
    public function addTestMessage($message, $data = null, $type = Output::MESSAGE)
    {
        if (!isset($this->_testMessages[$type])) {
            $this->_testMessages[$type] = array();
        }

        $this->_testMessages[$type][] = array(
            'message' => $message,
            'data'    => $data
        );
    }

    /**
     * Returns messages associated with the test.
     *
     * @return  array
     */
    public function getTestMessages()
    {
        return $this->_testMessages;
    }

    /**
     * Returns a count of the total assertions.
     *
     * @return  integer
     */
    public function assertionCount(/* ... */)
    {
        return $this->_assertionCount;
    }

    /**
     * Returns a count of failed assertions.
     *
     * @return  integer
     */
    public function failedAssertions(/* ... */)
    {
        return $this->_assertionFail;
    }

    /**
     * Returns a count of passed assertions.
     *
     * @return  integer
     */
    public function passedAssertions(/* ... */)
    {
        return $this->_assertionPass;
    }
    
    /**
     * Returns a count of skipped assertions.
     *
     * @return  integer
     */
    public function skippedAssertions(/* ... */)
    {
        return $this->_assertionSkip;
    }
    
    /**
     * Returns array of passed assertion tests.
     *
     * @return  array
     */
    public function getPassedAssertions(/* ... */)
    {
        return $this->_passedAssertions;
    }
    
    /**
     * Returns array of failed assertion tests.
     *
     * @return  array
     */
    public function getFailedAssertions(/* ... */)
    {
        return $this->_failedAssertions;
    }

    /**
     * Returns array of skipped assertion tests.
     *
     * @return  array
     */
    public function getSkippedAssertions(/* ... */)
    {
        return $this->_skippedAssertions;
    }

    /**
     * Registers the suite this test belongs to.
     *
     * @return  void
     */
    public function setSuite($suite)
    {
        $this->_suite = $suite;
    }

    /**
     * Returns the suite this test belongs to.
     *
     * @return  \prggmrunit\Suite
     */
    public function getSuite()
    {
        return $this->_suite;
    }
}
