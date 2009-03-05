<?php

/**
 * CallbackManager definition
 *
 * PHP version 5
 *
 * LICENSE: The contents of this file are subject to the Mozilla Public License Version 1.1
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is Red Tree Systems Code.
 *
 * The Initial Developer of the Original Code is Red Tree Systems, LLC. All Rights Reserved.
 *
 * @category     UI
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/exceptions/StopException.php';

abstract class CallbackManager
{
    /**
     * Array of named callbacks
     *
     * @see addCallback, dispatchCallback, marshallCallback
     */
    private $callbacks = array();

    final public function addCallback($name, $callable)
    {
        if (! array_key_exists($name, $this->callbacks)) {
            $this->callbacks[$name] = array();
        }
        array_push($this->callbacks[$name], $callable);
    }

    /**
     * Calls each callback in the named callback list.
     *
     * If a callback throws a StopException, it halts the execution of the list
     *
     * @param name string
     * @return mixed if a StopException is raised, it is returned, null otherwise
     * @see addCallback
     */
    final public function dispatchCallback($name)
    {
        if (! array_key_exists($name, $this->callbacks)) {
            return;
        }
        $args = array_slice(func_get_args(), 1);
        try {
            foreach ($this->callbacks[$name] as $call) {
                call_user_func_array($call, $args);
            }
        } catch (StopException $s) {
            return $s;
        }
    }

    /**
     * Like dispatchCallback, but collects all non-null return values from the
     * callbacks and returns an array of them.
     *
     * @param name string
     * @return mixed StopException as in dispatchCallback, array otherwise
     * @see dispatchCallback
     */
    final public function marshallCallback($name)
    {
        if (! array_key_exists($name, $this->callbacks)) {
            return;
        }
        $args = array_slice(func_get_args(), 1);
        $ret = array();
        try {
            foreach ($this->callbacks[$name] as $call) {
                $r = call_user_func_array($call, $args);
                if (isset($r)) {
                    array_push($ret, $r);
                }
            }
        } catch (StopException $s) {
            return $s;
        }
        return $ret;
    }

    /**
     * Like marshallCallback, except stops on the first non-null return and
     * returns it rather than collecting an array
     *
     * @param name string
     * @return mixed StopException as in dispatchCallback, mixed otherwise
     * @see dispatchCallback
     */
    final public function marshallSingleCallback($name)
    {
        if (! array_key_exists($name, $this->callbacks)) {
            return;
        }
        $args = array_slice(func_get_args(), 1);
        try {
            foreach ($this->callbacks[$name] as $call) {
                $r = call_user_func_array($call, $args);
                if (isset($r)) {
                    return $r;
                }
            }
        } catch (StopException $s) {
            return $s;
        }
        return null;
    }
}

?>
