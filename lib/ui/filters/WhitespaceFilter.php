<?php

/**
 * Whitespace Filter class definition
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
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Trims whitespace from the output text. Lifted from PHP Savant.
 *
 * @package        UI
 */
class WhitespaceFilter implements IOutputFilter
{
    /**
    *
    * Removes extra white space within the text.
    *
    * Trim leading white space and blank lines from template source
    * after it gets interpreted, cleaning up code and saving bandwidth.
    * Does not affect <pre></pre>, <script></script>, or
    * <textarea></textarea> blocks.
    *
    * @access public
    * @param string $output The source text to be filtered.
    * @return void
    */
    public function filterOutput(&$output)
    {
        $match = array();

        // Pull out the script blocks
        preg_match_all("!<script[^>]+>.*?</script>!is", $output, $match);
        $script_blocks = $match[0];
        $output = preg_replace(
            "!<script[^>]+>.*?</script>!is",
            '@@@FRAMEWORK:TRIM:SCRIPT@@@',
            $output
        );

        // Pull out the pre blocks
        preg_match_all("!<pre[^>]*>.*?</pre>!is", $output, $match);
        $pre_blocks = $match[0];
        $output = preg_replace(
            "!<pre[^>]*>.*?</pre>!is",
            '@@@FRAMEWORK:TRIM:PRE@@@',
            $output
        );

        // Pull out the textarea blocks
        preg_match_all("!<textarea[^>]+>.*?</textarea>!is", $output, $match);
        $textarea_blocks = $match[0];
        $output = preg_replace(
            "!<textarea[^>]+>.*?</textarea>!is",
            '@@@FRAMEWORK:TRIM:TEXTAREA@@@',
            $output
        );

        // remove all leading spaces, tabs and carriage returns NOT
        // preceeded by a php close tag.
        $output = trim(preg_replace('/(\S+)(?:\s{2,})/m', '\1', $output));

        // replace script blocks
        $this->replace(
            "@@@FRAMEWORK:TRIM:SCRIPT@@@",
            $script_blocks,
            $output
        );

        // replace pre blocks
        $this->replace(
            "@@@FRAMEWORK:TRIM:PRE@@@",
            $pre_blocks,
            $output
        );

        // replace textarea blocks
        $this->replace(
            "@@@FRAMEWORK:TRIM:TEXTAREA@@@",
            $textarea_blocks,
            $output
        );
    }

    /**
    *
    * Does a simple search-and-replace on the source text.
    *
    * @access protected
    * @param string $search The string to search for.
    * @param string $replace Replace with this text.
    * @param string &$output The source text.
    * @return string The text after search-and-replace.
    */
    protected function replace($search, $replace, &$output)
    {
        $len = strlen($search);
        $pos = 0;
        $count = count($replace);

        for ($i = 0; $i < $count; $i++) {
            // does the search-string exist in the buffer?
            $pos = strpos($output, $search, $pos);
            if ($pos !== false) {
                // replace the search-string
                $output = substr_replace($output, $replace[$i], $pos, $len);
            }
            else {
                break;
            }
        }

        return $output;
    }
}

?>
