<?php

/**
 * UiTag class definition
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
class UiTag extends Tag
{
    public function pageBuffer(DOMElement &$element)
    {
        $area = $this->requiredAttr($element, 'area', false);
        $clear = $this->getBooleanAttr($element, 'clear', true);

        $this->compiler->write("<?php // Page Buffer: $area\n");
        $this->compiler->write("  print \$page->getBuffer('$area');\n");
        if ($clear) {
            $this->compiler->write("  \$page->clearBuffer('$area');\n");
        }
        $this->compiler->write("?>");
    }

    public function warnings(DOMElement &$element)
    {
        $whence = $this->getUnquotedAttr($element, 'layout', '$current');
        $containerId = $this->getUnquotedAttr($element, 'containerid', 'warnings-container');
        $warningClass = $this->getUnquotedAttr($element, 'warningclass', 'warning');

        $this->compiler->write('<?php if (count('.$whence.'->getWarnings())) { ?>');
        $this->compiler->write('<div id="'.$containerId.'">');
        $this->compiler->write('<?php foreach ('.$whence.'->getWarnings() as $w) { ?>');
        $this->compiler->write('<div class="'.$warningClass.'"><?php echo $w ?></div>');
        $this->compiler->write('<?php } ?>');
        $this->compiler->write('</div>');
        $this->compiler->write('<?php '.$whence.'->clearWarnings(); ?>');
        $this->compiler->write('<?php } ?>');
    }

    public function notices(DOMElement &$element)
    {
        $whence = $this->getUnquotedAttr($element, 'layout', '$current');
        $containerId = $this->getUnquotedAttr($element, 'containerid', 'notices-container');
        $noticeClass = $this->getUnquotedAttr($element, 'noticeclass', 'notice');

        $this->compiler->write('<?php if (count('.$whence.'->getNotices())) { ?>');
        $this->compiler->write('<div id="'.$containerId.'">');
        $this->compiler->write('<?php foreach ('.$whence.'->getNotices() as $w) { ?>');
        $this->compiler->write('<div class="'.$noticeClass.'"><?php echo $w ?></div>');
        $this->compiler->write('<?php } ?>');
        $this->compiler->write('</div>');
        $this->compiler->write('<?php '.$whence.'->clearNotices(); ?>');
        $this->compiler->write('<?php } ?>');
    }

    public function addAssets(DOMElement &$element)
    {
        foreach ($element->childNodes as $n) {
            if ($n->nodeType == XML_ELEMENT_NODE) {
                switch ($n->tagName) {
                case 'script':
                    $href = $this->requiredAttr($n, 'href');
                    $type = $this->getAttr($n, 'type');
                    $this->compiler->write(
                        '<?php $page->addAsset(new WebPageScript('.
                        $href.
                        (isset($type) ? ", $type" : '').
                        ')); ?>'
                    );
                    break;
                case 'stylesheet':
                    $href = $this->requiredAttr($n, 'href');
                    $alt = $this->getBooleanAttr($n, 'alternate');
                    $title = $this->getAttr($n, 'title');
                    $media = $this->getAttr($n, 'media');

                    $args = array($href);
                    array_push($args, $alt ? 'true' : 'false');
                    if (isset($title)) {
                        array_push($args, $title);
                    } elseif (isset($media)) {
                        array_push($args, 'null');
                    }
                    if (isset($media)) {
                        array_push($args, $media);
                    }
                    $this->compiler->write(
                        '<?php $page->addAsset(new WebPageStylesheet('.
                        implode(', ', $args).')); ?>'
                    );
                    break;
                case 'link':
                    $href = $this->requiredAttr($n, 'href');
                    $rel = $this->requiredAttr($n, 'rel');
                    $type = $this->requiredAttr($n, 'type');
                    $title = $this->getAttr($n, 'title');
                    $this->compiler->write(
                        '<?php $page->addAsset(new WebPageLinkedResource('.
                        implode(', ', array(
                            $href, $type, $rel
                        )).
                        (isset($title) ? ", $title" : '').
                        ')); ?>'
                    );
                    break;
                case 'alternate':
                    $href = $this->requiredAttr($n, 'href');
                    $type = $this->requiredAttr($n, 'type');
                    $title = $this->getAttr($n, 'title');
                    $this->compiler->write(
                        '<?php $page->addAsset(new WebPageAlternateLink('.
                        implode(', ', array($href, $type
                        )).
                        (isset($title) ? ", $title" : '').
                        ')); ?>'
                    );
                    break;
                default:
                    throw new RuntimeException(
                        'Unknown asset element '.$n->tagName
                    );
                }
            }
        }
    }
}

?>
