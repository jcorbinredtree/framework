<?php

/**
 * Image class definition
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
 * @category     Util
 * @author         Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license        MPL 1.1
 * @version        1.0
 * @link             http://framework.redtreesystems.com
 */

/**
 * Represents a binary image
 *
 * @static
 * @category     Image
 * @package        Utils
 */
class Image
{
    /**
     * The actual binary of the image
     *
     * @var string
     */
    private $binary = '';

    public $width;

    public $height;

    public function __construct($binary=null)
    {
        if ($binary) {
            $this->setBinary($binary);
        }
    }

    public function __toString()
    {
        return $this->binary;
    }

    public function getBinary()
    {
        return $this->binary;
    }

    public function setBinary($binary)
    {
        $this->binary = $binary;

        if ($image = @imagecreatefromstring($this->binary)) {
            $this->width = imagesx($image);
            $this->height = imagesy($image);
            return true;
        }

        /*
         * broken GD?
         */
        {
            $name = tempnam('/tmp', 'broken-gd');
            file_put_contents($name, $this->binary);

            list($this->width, $this->height) = getimagesize($name);

            unlink($name);

            return true;
        }

        return false;
    }

    public function limit($width, $height)
    {
        if ($this->width > $width) {
            $this->setBinary($this->useToolboxToLimit($width, 0));
        }

        if ($this->height > $height) {
            $this->setBinary($this->useToolboxToLimit(0, $height));
        }
    }

    public function download($mimeType)
    {
        $length = (int) strlen($this->binary);
        if (!$length) {
            return;
        }

        header("Content-Type: $mimeType");
        header("Content-Length: $length");

        print $this->binary;
    }

    private function useToolboxToLimit($width, $height)
    {
        $name = tempnam('/tmp', 'lame-toolbox');

        file_put_contents($name, $this->binary);

        $image = new Image_Toolbox($name);
        $image->newOutputSize((int) $width, (int) $height, 1);

        $binary = $image->asString();

        /*
        header('Content-Type: image/jpeg');
        print $binary;
        exit(0);
        */

        unlink($name);

        return $binary;
    }
}

?>
