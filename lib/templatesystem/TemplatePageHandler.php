<?php

/**
 * TemplatePageHandler class definition
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
 * @category     TemplateSystem
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Provides template interface to Page
 */
class TemplatePageHandler extends PHPSTLNSHandler
{
    /**
     * Makes things like:
     *   <core:if page:test="isBufferEmpty:name" />
     * Work as you would exect
     */
    public function handleAttrTest(DOMAttr $attr)
    {
        $val = $attr->value;
        if (($i = strpos($val, ':')) !== false) {
            $type = trim(substr($val, 0, $i));
            $val = trim(substr($val, $i+1));
        } else {
            throw new PHPSTLCompilerException($this->compiler, 'missing value');
        }
        switch ($type) {
        case 'hasBuffer':
            return '${page.hasBuffer('.$this->quote($val).')}';
            break;
        case 'hasData':
            return '${page.hasData('.$this->quote($val).')}';
            break;
        default:
            throw new PHPSTLCompilerException($this->compiler,
                "invalid test type $type"
            );
            break;
        }
    }

    /**
     * Outputs the contents of a named page buffer
     *
     * Attributes:
     *   name  string  required the name of the page buffer
     *   clear boolean optional default true, clear the buffer afterwords
     *
     * @see Page::getBuffer, Page::clearBuffer
     *
     * @param DOMElement element the tag such as <page:buffer />
     * @return void
     */
    public function handleElementBuffer(DOMElement $element)
    {
        $area = $this->requiredAttr($element, 'area');
        $clear = $this->getBooleanAttr($element, 'clear', true);
        $clear = $clear ? 'true' : 'false';
        $this->compiler->write(
            "<?php print \$page->getBuffer($area, false, $clear); ?>"
        );
    }

    /**
     * Gets a page data item
     *
     * @param var string required
     * @param name string required
     * @param default mixed optional
     */
    public function handleElementData(DOMElement $element)
    {
        $var = $this->requiredAttr($element, 'var', false);
        $name = $this->requiredAttr($element, 'name');
        $default = $this->getAttr($element, 'default');
        $this->compiler->write("<?php \$$var = \$page->getData($name); ?>");
        if (isset($default)) {
            $this->compiler->write(
                "<?php if (!isset(\$$var)) \$$var = $default; ?>"
            );
        }
    }

    protected function handleElement(DOMElement $element)
    {
        switch ($element->localName) {
        case 'notices':
        case 'warnings':
            $this->getMessList($element, $element->localName);
            break;
        default:
            parent::handleElement($element);
            break;
        }
    }

    private function getMessList(DOMElement $element, $type)
    {
        $tsys = Site::getModule('TemplateSystem');
        if (! $tsys->hasModule('PageSystem')) {
            throw new RuntimeException(
                "cannot get $type, PageSystem not available"
            );
        }

        $get = 'get'.ucfirst($type);
        $clear = 'clear'.ucfirst($type);
        $contClass = $this->getUnquotedAttr($element, 'class', "$type-container");
        $this->compiler->write("<?php\n".
            "if (count(\$page->$get())) { \n".
            "  ?><ul class=\"$contClass\"><?php\n".
            "  foreach (\$page->$get() as \$w) {\n".
            "    ?><li><?php echo \$w ?></li><?php\n".
            "  } \n?></ul><?php\n".
            "  \$page->$clear();\n".
            "}\n".
        "?>");
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
    public function handleElementAddAssets(DOMElement $element)
    {
        $this->compiler->write(
            "<?php if (! \$page instanceof HTMLPage) {\n".
            "  throw new RuntimeException('Can only add html assets to an html page');\n".
            '} ?>'
        );
        $assets = array();
        foreach ($element->childNodes as $n) {
            if ($n->nodeType == XML_ELEMENT_NODE) {
                $href = $this->requiredAttr($n, 'href', false);
                $path = CurrentPath::get();
                if (
                    isset($path) &&
                    $this->needsQuote($href) &&
                    ! preg_match('~^(?:\w+://|/)~', $href)
                ) {
                    $href = (string) $path->url->down($href);
                }
                $href = $this->quote($href);
                switch ($n->tagName) {
                case 'script':
                    $asset = array('HTMLPageScript', $href);
                    $type = $this->getAttr($n, 'type');
                    if (isset($type)) {
                        array_push($asset, $type);
                    }
                    break;
                case 'stylesheet':
                    $alt = $this->getBooleanAttr($n, 'alternate');
                    $title = $this->getAttr($n, 'title');
                    $media = $this->getAttr($n, 'media');

                    $asset = array('HTMLPageStylesheet', $href);
                    array_push($asset, $alt ? 'true' : 'false');
                    if (isset($title)) {
                        array_push($asset, $title);
                    } elseif (isset($media)) {
                        array_push($asset, 'null');
                    }
                    if (isset($media)) {
                        array_push($asset, $media);
                    }
                    break;
                case 'link':
                    $rel = $this->requiredAttr($n, 'rel');
                    $type = $this->requiredAttr($n, 'type');
                    $title = $this->getAttr($n, 'title');
                    $asset = array('HTMLPageLinkedResource', $href, $type, $rel);
                    if (isset($title)) {
                        array_push($asset, $title);
                    }
                    break;
                case 'alternate':
                    $type = $this->requiredAttr($n, 'type');
                    $title = $this->getAttr($n, 'title');
                    $asset = array('HTMLPageAlternateLink', $href, $type);
                    if (isset($title)) {
                        array_push($asset, $title);
                    }
                    break;
                default:
                    throw new RuntimeException(
                        'Unknown asset element '.$n->tagName
                    );
                }
                array_push($assets, sprintf('new %s(%s)',
                    array_shift($asset), implode(', ', $asset)
                ));
            }
        }
        if (count($assets)) {
            $buffer = '';
            foreach ($assets as $asset) {
                $buffer .= "\$page->addAsset($asset);\n";
            }
            $this->compiler->write("<?php\n$buffer?>");
        }
    }
}

?>
