<?php
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



// library version
define('PRGGMRUNIT_VERSION', '0.2.0');

// The creator
define('PRGGMRUNIT_MASTERMIND', 'Nickolas Whiting');

$prggmrunitpath = dirname(realpath(__FILE__));
if (!class_exists('Prggmr')) {
    require_once $prggmrunitpath.'/../prggmr/lib/prggmr.php';
}

if (!version_compare(\Prggmr::version(), '0.2.0', '<=')) {
    exit('prggmrunit requires prggmr v0.2.0');
}

require $prggmrunitpath.'/engine.php';
require $prggmrunitpath.'/events.php';
require $prggmrunitpath.'/test.php';
require $prggmrunitpath.'/suite.php';
require $prggmrunitpath.'/api.php';
require $prggmrunitpath.'/output.php';
require $prggmrunitpath.'/emulator.php';
require $prggmrunitpath.'/emulator/test.php';

class Prggmrunit extends \prggmrunit\Engine {
    
    /**
     * @var  object|null  Instanceof the singleton
     */
    private static $_instance = null;

    /**
     * Returns instance of the Prggmrunit API.
     */
    final public static function instance(/* ... */)
    {
        if (null === static::$_instance) {
            static::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Returns the current version of prggmrunit.
     *
     * @return  string
     */
    final public static function version(/* ... */)
    {
        return PRGGMRUNIT_VERSION;
    }
}