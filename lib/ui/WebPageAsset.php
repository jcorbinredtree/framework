<?php

/**
 * WebPageAsset definition
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
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * A WebPage asset such as a script, stylesheet, etc
 *
 * @package UI
 */

abstract class WebPageAsset
{
    abstract public function __tostring();

    abstract public function compare($other);
}

class WebPageScript extends WebPageAsset
{
    public $href;
    public $type;

    public function __construct($href, $type='text/javascript')
    {
        $this->href = $href;
        $this->type = $type;
    }

    public function compare($other)
    {
        if (! isset($other) || ! is_a($other, 'WebPageScript')) {
            return false;
        }

        return
            $other->href == $this->href &&
            $other->type == $this->type;
    }

    public function __tostring()
    {
        return
            '<script type="'.
            htmlentities($this->type).
            '" src="'.
            htmlentities($this->href).
            '"></script>';
    }
}

class WebPageLinkedResource extends WebPageAsset
{
    public $href;
    public $type;
    public $rel;
    public $title;

    public function __construct($href, $type, $rel, $title=null)
    {
        $this->href = $href;
        $this->type = $type;
        $this->rel = $rel;
        $this->title = $title;
    }

    public function compare($other)
    {
        if (! isset($other) || ! is_a($other, 'WebPageLinkedResource')) {
            return false;
        }

        return
            $other->href == $this->href &&
            $other->type == $this->type &&
            $other->rel == $this->rel &&
            $other->title == $this->title;
    }

    public function __tostring()
    {
        $s = '<link rel="'.htmlentities($this->rel).'"';
        $s .= ' type="'.htmlentities($this->type).'"';
        $s .= ' href="'.htmlentities($this->href).'"';
        if (isset($this->title)) {
            $s .= ' title="'.htmlentities($this->title).'"';
        }
        $s .= ' />';
        return $s;
    }
}

class WebPageStylesheet extends WebPageLinkedResource
{
    public $media;

    public function __construct($href, $alt=false, $title=null, $media=null)
    {
        if ($alt) {
            $rel = 'alternate stylesheet';
        } else {
            $rel = 'stylesheet';
        }
        parent::__construct($href, 'text/css', $rel, $title);
        $this->media = $media;
    }

    public function __tostring()
    {
        $s = parent::__tostring();
        if (isset($this->media)) {
            $rel = "rel=\"$this->rel\"";
            $i = strpos($s, $rel) + strlen($rel);
            $s = substr($s, 0, $i)." media=\"$this->media\"". substr($s, $i);
        }
        return $s;
    }
}

class WebPageAlternateLink extends WebPageLinkedResource
{
    public function __construct($href, $type, $title=null)
    {
        parent::__construct($href, $type, 'alternate', $title);
    }
}

?>
