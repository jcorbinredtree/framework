<?php

/**
 * Tag base class definition
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
 * The Initial Developer of the Original Code is
 * Brandon Prudent <php-stl@redtreesystems.com>. All Rights Reserved.
 *
 * @category     Tag
 * @author       Red Tree Systems, LLC <php-stl@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.4
 * @link         http://php-stl.redtreesystems.com
 */

/**
 * Tag
 * 
 * This class provides a tag handler base class
 *
 * @category     Tag
 */
abstract class Tag
{
    /**
     * The compiler to write to
     *
     * @var Compiler
     */
    protected $compiler;
    
    /**
     * Constructor
     *
     * @param Compiler $compiler 
     */
    public function __construct(Compiler &$compiler)
    {
        $this->compiler = $compiler;
    }
    
    /**
     * Requires the attribute to be on $element
     *
     * @param DOMElement $element the target element
     * @param string $attr the attribute key
     * @param boolean $quote true if the value should be quoted [default]
     * @return the attribute value for key $attr
     */
    protected function requiredAttr(DOMElement &$element, $attr, $quote=true)
    {
        if (!$element->hasAttribute($attr)) {
            die("required attribute $attr missing from element $element->nodeName");
        }
        
        return ($quote ? $this->quote($element->getAttribute($attr)) : $element->getAttribute($attr));
    }
    
    /**
     * Get an attribute from $element
     *
     * @param DOMElement $element the target element
     * @param string $attr the attribute key
     * @param mixed $default the default value
     * @return the attribute value for key $attr
     */
    protected function getAttr(DOMElement &$element, $attr, $default=null)
    {
        return $this->quote($element->hasAttribute($attr) ? $element->getAttribute($attr) : $default);
    }
    
    /**
     * Get a raw attribute from $element
     *
     * @param DOMElement $element the target element
     * @param string $attr the attribute key
     * @param mixed $default the default value
     * @return the attribute value for key $attr
     */
    protected function getUnquotedAttr(DOMElement &$element, $attr, $default=null)
    {
        return ($element->hasAttribute($attr) ? $element->getAttribute($attr) : $default);
    }    
    
    /**
     * Get a boolean attribute from $element
     *
     * @param DOMElement $element the target element
     * @param string $attr the attribute key
     * @param mixed $default the default value
     * @return boolean a value matching the users intent
     */
    protected function getBooleanAttr(DOMElement &$element, $attr, $default=false)
    {
        if (!$element->hasAttribute($attr)) {
            return $default;
        }
        
        switch ($element->getAttribute($attr)) {
            case 'true':
            case 'yes':
                return true;
            case 'false':
            case 'no':
                return false;
        }
        
        die("Invalid boolean attribute $attr specified for $element->nodeName");
    }    
    
    /**
     * Processes child elements
     *
     * @param DOMElement $element
     * @return void
     */
    protected function process(DOMElement &$element)
    {
        if ($element->hasChildNodes()) {
            foreach($element->childNodes as $node) {
                $this->compiler->process($node);
            }
        }
    }
    
    /**
     * Quotes a subject if it's found to require one
     *
     * @param string $val The subject to quote (or not)
     * @return string The quoted (or not) value
     */
    protected function quote($val)
    {
        if ($this->needsQuote($val)) {
            return "'$val'";
        }
        
        return $val;
    }
    
    /**
     * Returns true if the value requires quoting
     * 
     * @return boolean
     */
    protected function needsQuote($val)
    {
        $char = strlen($val) ? $val[0] : '';
        
        return (($char != '$') && ($char != '@'));
    }    
}

?>
