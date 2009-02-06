<?php

/**
 * AsyncUnitTest class definition
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
 * @category     Tests
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * An asynchronous unit test, basically the notion is that you're testing some
 * codepath that fires callbacks.
 */
class ASyncUnitTest extends UnitTestCase
{
    protected $expectationStack = array();

    /**
     * Pushes an expectation onto the stack
     *
     * @param expectation Expectation object to verify when data is available
     * @param multiplicity int each time verify is ran, it will decrement the
     *        expectation's multiplicity, once it reaches zero, it's poped from
     *        the stack.
     * @param message string optional string, if null assert's default will be
     *        used
     *
     * @return void
     */
    public function expect(&$expectation, $multiplicity=1, $message=null)
    {
        if (! is_int($multiplicity) || $multiplicity <= 0) {
            throw new InvalidArgumentException(
                "Invalid expectation multiplicity"
            );
        }

        array_push($this->expectationStack, array(
            $expectation, $multiplicity, $message
        ));
    }


    /**
     * Whether we're expecting anything
     *
     * @return boolean true if there are any expectations on the stack
     */
    public function expecting()
    {
        if (count($this->expectationStack)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks the given value against the expectation set by expect
     *
     * @param compare mixed value to compare
     * @param message string optional custom message to use
     *
     * @return boolean true on pass
     */
    public function verify($compare, $message=null)
    {
        if (! $this->expecting()) {
            throw new RuntimeException("No expectation to verify");
        }

        // reference to the head
        $ex =& $this->expectationStack[0];

        // if message not specified to verify(), use what was provided in expect()
        if (! isset($message)) {
            $message = $ex[2];
        }

        // test the expectation
        if (isset($message)) {
            $r = $this->assert($ex[0], $compare, $message);
        } else {
            $r = $this->assert($ex[0], $compare);
        }

        // decrement multiplicity
        if (--$ex[1] < 1) {
            array_shift($this->expectationStack);
        }

        return $r;
    }
}

?>
