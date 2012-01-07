<?php
namespace prggmrunit\emulator;
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
        $result = $this->_assert($name, $args, $this->_framework);

        if (defined('PRGGMRUNIT_EMULATION_DEBUG')) {
            \prggmrunit\Output::send(sprintf(
                'Ran assertion %s results (%s)%s',
                \prggmrunit\Output::variable($name),
                ($result === true) ? 'pass' : ($result === false) ? 'fail' : 'assertion not avaliable',
                PHP_EOL
            ));
            if ($result !== true) {
                // everything fails here
                if ($result === null) {
                     \prggmrunit\Output::send(sprintf(
                        "Emulation framework %s assertion %s does not exist.%s",
                         \prggmrunit\Output::variable($this->_framework), Output::variable($name), PHP_EOL
                    ),  \prggmrunit\Output::DEBUG);
                     \prggmrunit\Output::send(sprintf(
                        "Avaliable emulation assertions %s%s%s",
                        PHP_EOL,
                        implode(PHP_EOL, array_keys(static::$_assertions[$framework])),
                        PHP_EOL
                    ),  \prggmrunit\Output::DEBUG);
                } else {
                     \prggmrunit\Output::send(sprintf(
                        "Emulation Assertion %s::%s failed.%s",
                        $this->_framework,
                        \prggmrunit\Output::variable($name),
                        PHP_EOL,
                        PHP_EOL
                    ),  \prggmrunit\Output::DEBUG);
                }
                 \prggmrunit\Output::send(sprintf(
                    "Params%s%s%s",
                    PHP_EOL,
                     \prggmrunit\Output::variable($args),
                    PHP_EOL
                ),  \prggmrunit\Output::DEBUG);
                 \prggmrunit\Output::backtrace(debug_backtrace());
            }
        }
        
        return $result;
    }
}
