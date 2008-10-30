<?php

/**
 * Administration Tag class definition
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
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

class AdministrationTag extends FrameworkTag
{
    public function href(DOMElement &$element)
    {
        $component = $this->requiredAttr($element, 'driver');
        $action = $this->requiredAttr($element, 'action');
        $args = $this->getUnquotedAttr($element, 'args');
        $stage = $this->getUnquotedAttr($element, 'stage', Stage::VIEW);        
        $var = $this->getUnquotedAttr($element, 'var', false);

        $href = 'Administration::getActionURI(' . "$component,$action,";

        if ($args) {
            $href .= $this->parseArgs($args);
            $href .= ",$stage";            
        }
        else {
            $href .= "array(),$stage";            
        }

        $href .= ');';
        if ($var) {
            $this->compiler->write('<?php $' . "$var = $href ?>");
        }
        else {
            $this->compiler->write("<?php echo $href ?>");
        }
    }

    public function link(DOMElement &$element)
    {
        $component = $this->requiredAttr($element, 'driver');
        $action = $this->requiredAttr($element, 'action');
        $args = $this->getUnquotedAttr($element, 'args');
        $stage = $this->getUnquotedAttr($element, 'stage', Stage::VIEW);        

        $a = '<a href = "<?php echo Administration::getActionURI(' . "$component,$action,";

        if ($args) {
            $a .= $this->parseArgs($args);
            $a .= ",$stage";            
        }
        else {
            $a .= "array(),$stage";            
        }

        $a .= ');?>"' . $this->optionalAttributes($element, array('name', 'class', 'style', 'id', 'title'));
        $a .= '>';

        $this->compiler->write($a);
        $this->process($element);
        $this->compiler->write('</a>');
    }
    
    public function icon(DOMElement &$element)
    {
        $name = $this->requiredAttr($element, 'name', false);
        $alt = $this->requiredAttr($element, 'alt', false);
        
        $this->compiler->write('<img src = "<?php echo $config->absUri; ?>/admin/view/icons/' . $name . '" ');
        $this->compiler->write('alt = "' . htmlentities($alt) . '" />');
    }
}

?>
