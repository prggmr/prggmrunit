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
 * Events signals.
 */
class Events {
    const START = 0xFB01;
    const END   = 0xFB02;
    const TEST_ASSERTION_FAIL = 0xFB03;
    const TEST_ASSERTION_PASS = 0xFB04;
    const TEST_ASSERTION_SKIP = 0xFB05;
    const EMULATION_LOAD = 0xFB06;
    const TEST = 0xFB07;
}