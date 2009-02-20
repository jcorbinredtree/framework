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
    /**
     * Outputs the contents of a named page buffer
     *
     * Attributes:
     *   name  string  required the name of the page buffer
     *   clear boolean optional default true, clear the buffer afterwords
     *
     * @see SitePage::getBUffer, SitePage::clearBuffer
     *
     * @param DOMElement element the tag such as <ui:pageBuffer />
     * @return void
     */
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

    /**
     * Outputs the list of current warnings if any, then clears the list of
     * current warnings.
     *
     * Attributes:
     *   class string optional, defaults to 'warnings-container'
     *
     * Outputs something like:
     *   <ul class="$class"><li>...</li></ul>
     *
     *
     * @param DOMElement element the tag such as <ui:pageBuffer />
     * @return void
     */
    public function warnings(DOMElement &$element)
    {
        $contClass = $this->getUnquotedAttr($element, 'class', 'warnings-container');

        $this->compiler->write('<?php if (count($current->getWarnings())) { ?>');
        $this->compiler->write('<ul class="'.$contClass.'">');
        $this->compiler->write('<?php foreach ($current->getWarnings() as $w) { ?>');
        $this->compiler->write('<li><?php echo $w ?></li>');
        $this->compiler->write('<?php } ?>');
        $this->compiler->write('</ul>');
        $this->compiler->write('<?php $current->clearWarnings(); ?>');
        $this->compiler->write('<?php } ?>');
    }

    /**
     * Outputs the list of current notices if any, then clears the list of
     * current notices.
     *
     * Attributes:
     *   class string optional, defaults to 'notices-container'
     *
     * Outputs something like:
     *   <ul class="$class"><li>...</li></ul>
     *
     * @param DOMElement element the tag such as <ui:pageBuffer />
     * @return void
     */
    public function notices(DOMElement &$element)
    {
        $contClass = $this->getUnquotedAttr($element, 'class', 'notices-container');

        $this->compiler->write('<?php if (count($current->getNotices())) { ?>');
        $this->compiler->write('<ul class="'.$contClass.'">');
        $this->compiler->write('<?php foreach ($current->getNotices() as $w) { ?>');
        $this->compiler->write('<li><?php echo $w ?></li>');
        $this->compiler->write('<?php } ?>');
        $this->compiler->write('</ul>');
        $this->compiler->write('<?php $current->clearNotices(); ?>');
        $this->compiler->write('<?php } ?>');
    }

    /**
     * Adds assets to the page
     *
     * Expects child elements like:
     *   <script href="some.js" />
     *   <stylesheet href="some.css" />
     *   <link href="some/resource" rel="something" type="some/mime" />
     *   <alternate href="some.rss" type="application/rss+xml" title="RSS Feed" />
     *
     * If any href is a simple string (i.e. no ${...} expression), and it is
     * relative, it will be passed to CurrentPath->url->down to form an
     * absolute url.
     *
     * Full gory attribute details:
     *   script: a HTMLPageScript asset
     *     href string required
     *     type string optional default 'text/javascript'
     *
     *   stylesheet: a HTMLPageStylesheet asset
     *     href      string  required
     *     alternate boolean optional default false
     *     title     string  optional default null
     *     media     string  optional default null
     *
     *   alternate: a HTMLPageAlternateLink asset
     *     href  string required
     *     type  string required
     *     title string optional default null
     *
     *   link: a HTMLPageLinkedResource asset
     *     href  string required
     *     rel   string required
     *     type  string required
     *     title string optional default null
     *
     * @param DOMElement element the tag such as <ui:pageBuffer />
     * @return void
     */
    public function addAssets(DOMElement &$element)
    {
        $this->compiler->write(
            "<?php if (! is_a(\$page, 'HTMLPage')) {\n".
            "  throw new RuntimeException('Can only add html assets to an html page');\n".
            '} ?>'
        );
        foreach ($element->childNodes as $n) {
            if ($n->nodeType == XML_ELEMENT_NODE) {
                $href = $this->requiredAttr($n, 'href', false);
                if ($this->needsQuote($href) && ! preg_match('~^(?:\w+://|/)~', $href)) {
                    global $current;
                    $href = (string) $current->path->url->down($href);
                    $href = $this->quote($href);
                }
                switch ($n->tagName) {
                case 'script':
                    $type = $this->getAttr($n, 'type');
                    $this->compiler->write(
                        '<?php $page->addAsset(new HTMLPageScript('.
                        $href.
                        (isset($type) ? ", $type" : '').
                        ')); ?>'
                    );
                    break;
                case 'stylesheet':
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
                        '<?php $page->addAsset(new HTMLPageStylesheet('.
                        implode(', ', $args).')); ?>'
                    );
                    break;
                case 'link':
                    $rel = $this->requiredAttr($n, 'rel');
                    $type = $this->requiredAttr($n, 'type');
                    $title = $this->getAttr($n, 'title');
                    $this->compiler->write(
                        '<?php $page->addAsset(new HTMLPageLinkedResource('.
                        implode(', ', array(
                            $href, $type, $rel
                        )).
                        (isset($title) ? ", $title" : '').
                        ')); ?>'
                    );
                    break;
                case 'alternate':
                    $type = $this->requiredAttr($n, 'type');
                    $title = $this->getAttr($n, 'title');
                    $this->compiler->write(
                        '<?php $page->addAsset(new HTMLPageAlternateLink('.
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
