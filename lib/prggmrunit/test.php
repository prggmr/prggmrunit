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
     * Reference to the assertions avaliable to a test.
     *
     * @var  object
     */
    protected $_assertions = null;

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
     * @param  object  $engine  \prggmrunit\Assertions
     */
    public function __construct($assertions)
    {
        $this->_assertions = $assertions;
        // by default all tests are passed
        $this->setTestResult(self::PASS);
    }
    
    /**
     * Sets to skip a portion or all of the test.
     *
     * @return void
     */
    public function skip(/* ... */)
    {
        if ($this->getTestResult() !== self::FAIL) {
            $this->setTestResult(self::SKIP);
        }
    }
    
    /**
     * Begins test again if set to skip.
     *
     * @return  void
     */
    public function endSkip(/* ... */)
    {
        if ($this->getTestResult() !== self::FAIL) {
            $this->setTestResult(self::PASS);
        }
    }
    
    /**
     * Calls an assertion.
     */
    public function __call($name, $args = null)
    {
         return $this->_assert($name, $args, null, debug_backtrace(false));
    }
    
    /**
     * Runs an assertion test.
     *
     * @param  string  $name  Assertion test.
     * @param  array  $args  Arguments to pass to test.
     * @param  string  $namespace  Namespace assertion belongs.
     * @param  array  $backtrace  Backtrace of the assertion.
     *
     * @return  boolean
     */
    protected function _assert($name, $args = null, $namespace = null, $backtrace = null) 
    {
        $this->_assertionCount++;
        // skip if fail or skip state
        if ($this->getTestResult() === self::FAIL ||
            $this->getTestResult() === self::SKIP) {
            \prggmrunit::instance()->fire(Events::TEST_ASSERTION_SKIP, array($name, $args, $namespace, $this));
            $this->_skippedAssertions[] = $name;
            $this->_assertionSkip++;
            return false;
        }
        if (null === $args) {
            $args = array($this);
        } else {
            if (!is_array($args)) {
                $args = array($this, $args);
            } else {
                array_unshift($args, $this);
            }
        }
        try {
            $result = $this->_assertions->assert($name, $args, $namespace);
        } catch (\Exception $e) {
            $this->addTestMessage('Exception Encountered', $e->getMessage(), Output::ERROR);
            $result = false;
        }
        if (null === $result) {
            $suggestions = array_filter($this->_assertions->getAssertions($namespace), 
                function($var) use ($name){
                    if (\similar_text($var, $name) >= 5) return true;
                    return false;
                }
            );
            $this->addTestMessage(sprintf(
                '%s is not a valid assertion%s',
                $name,
                (count($suggestions) > 0) ? ' maybe you wanted (' . implode(", ", $suggestions).')?' : 
                ''
            ), $backtrace, Output::DEBUG);
            \prggmrunit::instance()->fire(Events::TEST_ASSERTION_SKIP, array($name, $args, $namespace, $this));
            $this->_skippedAssertions[] = $name;
            $this->_assertionSkip++;
            return false;
        } elseif ($result !== true) {
            $this->_failedAssertions[] = $name;
            \prggmrunit::instance()->fire(Events::TEST_ASSERTION_FAIL, array($name, $args, $namespace, $this));
            $this->_assertionFail++;
            // if fail add the backtrace
            $this->setTestResult(self::FAIL);
            $this->addTestMessage($result, $backtrace, Output::ERROR);
            return false;
        } else {
            $this->_passedAssertions[] = $name;
            \prggmrunit::instance()->fire(Events::TEST_ASSERTION_PASS, array($name, $args, $namespace, $this));
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
     * Registers if test belongs to a suite.
     *
     * @return  void
     */
    public function setSuite()
    {
        $this->_suite = true;
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
