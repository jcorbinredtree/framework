<?php

/**
 * Framework Tag class definition
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

class FrameworkTag extends Tag
{
    protected function parseArgs($args)
    {
        $args = explode(',', $args);

        $href = 'array(';
        for ($i = 0; $i < count($args); $i++) {
            list($name, $value) = explode('=', $args[$i]);

            if ($this->needsQuote($name)) {
                $name = "'$name'";
            }

            if ($this->needsQuote($value)) {
                $value = "'$value'";
            }

            $href .= "$name => $value";
            if (($i + 1) < count($args)) {
                $href .= ',';
            }
        }

        return $href .= ')';
    }

    protected function optionalAttributes(DOMElement &$element, $attrs)
    {
        $opts = '';

        foreach ($attrs as $attr) {
            if ($value = $this->getUnquotedAttr($element, $attr)) {
                $opts .= " $attr = " . '"' . $value . '"';
            }
        }

        return $opts;
    }

    public function href(DOMElement &$element)
    {
        global $config;

        $sefLink = $this->getUnquotedAttr($element, 'sefLink');
        $component = $this->getAttr($element, 'component');
        $action = $this->getAttr($element, 'action');
        $args = $this->getUnquotedAttr($element, 'args');
        $stage = $this->getUnquotedAttr($element, 'stage', Stage::VIEW);
        $var = $this->getUnquotedAttr($element, 'var', false);

        $href = '';
        if ($sefLink) {
            $href = "'$config->absUri/$sefLink'";
        }
        else {
            $href = '$this->href(' . "$component,$action,";

            if ($args) {
                $href .= $this->parseArgs($args);
                $href .= ",$stage";
            }
            else {
                $href .= "array(),$stage";
            }

            $href .= ');';
        }

        if ($var) {
            $this->compiler->write('<?php $' . "$var = $href ?>");
        }
        else {
            $this->compiler->write("<?php echo $href ?>");
        }
    }

    public function link(DOMElement &$element)
    {
        $sefLink = $this->getUnquotedAttr($element, 'sefLink');
        $component = $this->getAttr($element, 'component');
        $action = $this->getAttr($element, 'action');
        $args = $this->getUnquotedAttr($element, 'args');
        $stage = $this->getUnquotedAttr($element, 'stage', Stage::VIEW);

        $href = '';
        if ($sefLink) {
            $href = "'$config->absUri/content/$sefLink'";
        }
        else {
            $href = '<?php echo $this->href(' . "$component,$action,";

            if ($args) {
                $href .= $this->parseArgs($args);
                $href .= ",$stage";
            }
            else {
                $href .= "array(),$stage";
            }

            $href .= '); ?>';
        }

        $a = '<a href = "' . $href . '"';
        $a .= $this->optionalAttributes($element, array('name', 'class', 'style', 'id', 'title'));
        $a .= '>';

        $this->compiler->write($a);
        $this->process($element);
        $this->compiler->write('</a>');
    }

    public function pageSet(DOMElement &$element)
    {
        $pager = $this->requiredAttr($element, 'pager');

        $this->compiler->write('<?php echo ' . $pager . '->makeNavigation(); ?>');
    }

    public function getThemeIcon(DOMElement &$element)
    {
        $name = $this->requiredAttr($element, 'name');
        $alt = $this->requiredAttr($element, 'alt');
        $align = $this->getAttr($element, 'align');

        $img = '<img src="<?php echo $this->getThemeIcon(' . $name . '); ?>" alt="' . $alt . '"';

        if($align){
            $img .= ' align = "' . $align . '"';
        }

        $img .= "/>";

        $this->compiler->write($img);
    }

    public function form(DOMElement &$element)
    {
        global $current;

        $class = $current->component->getClass();
        $method = $this->getUnquotedAttr($element, 'method', 'post');
        $stage = $this->getUnquotedAttr($element, 'stage', Stage::VALIDATE);

        $form = '<form action = "<?php echo ' . $class . '::getActionURI($current->component->getClass(),$current->action->id,';
        $form .= "array('-secure'=>" . '$current->isSecureRequest()), ' . $stage . '); ?>" method = "' . $method . '"';
        $form .= $this->optionalAttributes($element, array('name', 'id', 'enctype'));
        $form .= '>';

        $this->compiler->write($form);
        $this->process($element);
        $this->compiler->write('</form>');
    }

    public function I18N(DOMElement &$element)
    {
        $value = $this->requiredAttr($element, 'value');
        $escapeXml = $this->getBooleanAttr($element, 'escapeXml', true);
        $var = $this->getUnquotedAttr($element, 'var', '');

        if ($escapeXml) {
            $I18N = '<?php htmlentities($this->eprint(I18N::String(' . $value . '))); ?>';
        }
        else{
            $I18N = '<?php $this->eprint(I18N::String(' . $value . ')); ?>';
        }
        if ($var) {
            $I18N =    '<?php $' . $var . ' = I18N::String(' . $value . '); ?>';
        }

        $this->compiler->write($I18N);
    }
}

?>
