<?php

/**
 * I18N class definition
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
 * @category     i18n
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/i18n/LangPack.php';

/**
 * A static class that manages languages and strings
 *
 * @static
 * @category     I18N
 * @package        Core
 */
class I18N
{
    /**
     * Holds the current language code
     *
     * @static
     * @access private
     * @var string
     */
    private static $lang = 'en';

    /**
     * Holds a LangPack instance, the current language
     *
     * @static
     * @access private
     * @var LangPack
     */
    private static $pack = null;

    /**
     * Constructor; Private
     *
     * @static
     * @access private
     * @return I18N a new instance
     */
    private function __construct()
    {

    }


    /**
     * Gets the specified language pack, and assigns it to
     * the static variable $pack.
     *
     * @static
     * @access private
     * @param string $lcLang the language code, lowercase
     * @param string $ucLang the language code, uppercase
     * @return LangPack the specified language pack, or
     * false if it could not be found
     */
    static private function GetLangPack($lcLang, $ucLang)
    {
        $path = "languages/langpack.$lcLang.php";
        $class = "I18N_$ucLang";

        @include_once $path;

        if (class_exists($class)) {
            return I18N::$pack = new $class();
        } else {
            return null;
        }
    }

    /**
     * Sets the current language
     *
     * @static
     * @access public
     * @param mixed $lang a language code, or language id
     * @return boolean true on success
     */
    static public function Set($lang)
    {
        global $config, $database;

        $lcLang = strtolower($lang);
        $ucLang = strtoupper($lang);
        I18N::$lang = $lcLang;

        if (!I18N::GetLangPack($lcLang, $ucLang)) {
            $config->fatal("Problem loading language pack '$lang'. Does it exist? Is it in the right place?");
            exit(1);
        }

        return true;
    }

    /**
     * Filters the string/key through the current language pack.
     *
     * @static
     * @access public
     * @param string $string the string/key you wish to retrieve a translation for
     * @return string the string/key filtered through the current language pack
     */
    static public function String($string)
    {
        return I18N::$pack->get($string);
    }
}

?>
