<?php

/**
 * BufferedObject class definition
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
 * @category     Core
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/util/IOutputFilter.php';

/**
 * Allows an object to be buffered through standard methods.
 * Obviously this class is worthless if clients don't behave.
 */
class BufferedObject
{
    /**
     * The filter chain
     *
     * @access protected
     * @var array
     */
    private $filters = array();

    /**
     * Provides the buffer for this object
     *
     * @access protected
     * @var string a binary buffer
     */
    protected $buffer = null;

    /**
     * Adds a filter to the output chain
     *
     * @param IOutputFilter $filter the filter to add
     * @return void
     */
    public function addFilter(IOutputFilter $filter)
    {
        array_push($this->filters, $filter);
    }

    /**
     * Removes a filter from the output chain
     *
     * @param IOutputFilter $remove the filter to remove
     * @return void
     */
    public function removeFilter(IOutputFilter $remove)
    {
        $filters = array();
        foreach ($this->filters as $filter) {
            if ($filter !== $remove) {
                array_push($filters, $filter);
            }
        }

        $this->filters = $filters;
    }

    /**
     * Sets the content of the buffer.
     *
     * Note that the previous contents will be destroyed.
     *
     * @access public
     * @param string $content
     * @return void
     */
    public function setBuffer($content)
    {
        assert(is_string($content) || !isset($content));
        $this->buffer = strlen($content) > 0 ? $content : null;
    }

    /**
     * Gets the content of the current buffer
     *
     * @access public
     * @return string the current buffer
     */
    public function getBuffer()
    {
        if (!isset($this->buffer)) {
            return '';
        }

        $buffer = $this->buffer;
        foreach ($this->filters as &$filter) {
            $buffer = $filter->filter($buffer);
        }
        return $buffer;
    }

    /**
     * Write the string to the current buffer
     *
     * @access public
     * @param string $str
     * @return void
     */
    public function write($str)
    {
        if (isset($this->buffer)) {
            $this->buffer .= $str;
        } else {
            $this->buffer = $str;
        }
    }

    /**
     * Empty the current buffer
     *
     * @access public
     * @return void
     */
    public function clear()
    {
        $this->buffer = null;
    }

    /**
     * Flushes the current buffer via a 'print', calling each filter as it was
     * added.
     *
     * Note that this also calls the clear() method.
     *
     * @access public
     * @return void
     */
    public function flush()
    {
        print $this->getBuffer();
        $this->clear();
    }

    /**
     * A simple method to simply view a template, optionally setting aruments
     *
     * @param string name the location of the template
     * @param array arguments [optional] the arguments to pass to the template,
     * expressed as name/value pairs
     * @return void
     */
    public function writeTemplate($template, $arguments=null)
    {
        $this->write(TemplateSystem::process($name, $arguments));
    }
}

?>
