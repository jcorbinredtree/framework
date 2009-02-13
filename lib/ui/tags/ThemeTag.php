<?php

/**
 * ThemeTag class definition
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
 * @package      UI
 * @category     Tags
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Simplifies theme construction by adding several standard tags
 *
 * @package      UI
 * @category     Tags
 */
class ThemeTag extends Tag
{
    public function allHead(DOMElement &$element)
    {
        $this->title($element);
        $this->keywords($element);
        $this->description($element);
        $this->scripts($element);
        $this->stylesheets($element);
        $this->head($element);
    }

    public function title(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');
        $default = $this->getAttr($element, 'default', '');

        $this->compiler->write('<title><?php echo (' . $layout . '->title ? ' . $layout . '->title : ' . $default . '); ?></title>');
    }

    public function head(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');

        $this->compiler->write('<?php echo ' . $layout . '->head; ?>');
    }

    public function keywords(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');

        $this->compiler->write('<?php if(' . $layout . '->keywords){?>');
        $this->compiler->write('<meta name = "keywords" content = "<?php echo ' . $layout . '->keywords; ?>" />');
        $this->compiler->write('<?php } ?>');
    }

    public function description(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');

        $this->compiler->write('<?php if(' . $layout . '->description){?>');
        $this->compiler->write('<meta name = "description" content = "<?php echo ' . $layout . '->description; ?>" />');
        $this->compiler->write('<?php } ?>');
    }

    public function scripts(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');

        $this->compiler->write('<?php foreach(' . $layout . '->scripts as $script){?>');
        $this->compiler->write('<script src = "<?php echo $script; ?>" type = "text/javascript"></script>');
        $this->compiler->write('<?php } ?>');
    }

    public function stylesheets(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');

        $this->compiler->write('<?php foreach(' . $layout . '->stylesheets as $ss){?>');
        $this->compiler->write('<link href = "<?php echo $ss; ?>" type = "text/css" rel = "stylesheet" />');
        $this->compiler->write('<?php } ?>');
    }

    public function warnings(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$page');
        $containerId = $this->getUnquotedAttr($element, 'containerid', 'warnings-container');
        $warningClass = $this->getUnquotedAttr($element, 'warningclass', 'warning');

        $this->compiler->write('<?php if (count(' . $layout . '->getWarnings())){ ?>');
        $this->compiler->write('<div id = "' . $containerId . '">');
        $this->compiler->write('<?php foreach(' . $layout . '->getWarnings() as $w){?>');
        $this->compiler->write('<div class = "' . $warningClass . '"><?php echo $w; ?></div>');
        $this->compiler->write('<?php } ?></div><?php } ?>');
    }

    public function notices(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$page');
        $containerId = $this->getUnquotedAttr($element, 'containerid', 'notices-container');
        $noticeClass = $this->getUnquotedAttr($element, 'noticeclass', 'notice');

        $this->compiler->write('<?php if (count(' . $layout . '->getNotices())){ ?>');
        $this->compiler->write('<div id = "' . $containerId . '">');
        $this->compiler->write('<?php foreach(' . $layout . '->getNotices() as $n){?>');
        $this->compiler->write('<div class = "' . $noticeClass . '"><?php echo $n ?></div>');
        $this->compiler->write('<?php } ?></div><?php } ?>');
    }

    public function breadcrumbs(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');
        $containerId = $this->getUnquotedAttr($element, 'containerid', 'breadcrumb-container');
        $breadcrumbClass = $this->getUnquotedAttr($element, 'breadcrumbclass', 'breadcrumb');

        $this->compiler->write('<?php $__bc=' . $layout . '->breadCrumbs;$__bc_c=count($__bc);if($__bc_c){ ?>');
        $this->compiler->write('<div id = "' . $containerId . '">');
        $this->compiler->write('<?php for($__i=0;$__i<$__bc_c;$__i++){$__bc_i=$__bc[$__i];?>');
        $this->compiler->write('<span class = "' . $breadcrumbClass . '">');
        $this->compiler->write('<?php if (($__i+1)<$__bc_c){ ?>');
        $this->compiler->write('<a href = "<?php echo $__bc_i->href; ?>"><?php echo $__bc_i->label; ?></a> &gt; ');
        $this->compiler->write('<?php } else { echo $__bc_i->label; } ?></span>');
        $this->compiler->write('<?php } ?></div><?php } ?>');
    }

    public function content(DOMElement &$element)
    {
        $layout = $this->getUnquotedAttr($element, 'layout', '$this->layout');

        $this->compiler->write('<?php echo ' . $layout . '->content; ?>');
    }
}

?>
