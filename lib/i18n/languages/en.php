<?php

/**
 * I18N_EN class definition
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
 * @category   I18N
 * @package    Core
 * @author     Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright  2006 Red Tree Systems, LLC
 * @license    http://www.redtreesystems.com PROPRITERY
 * @version    1.2
 * @link       http://www.redtreesystems.com
 */

/**
 * A class that manages the english language. This deviates a bit
 * from other language packs by the assumption that only english
 * strings will be passed in, and thus, returns the same.
 *
 * @category   I18N
 * @package    Core
 */
class I18N_EN extends LangPack
{
    private $translation = array();

    public function __construct()
    {
        $this->translation['_INTERNAL_ERROR'] = 'Internal Error';
    }

    public function get($string)
    {
        if (!isset($this->translation[$string])) {
            return $string;
        }

        return $this->translation[$string];
    }

    public function getLanguageName()
    {
        return 'US English';
    }
}

?>
