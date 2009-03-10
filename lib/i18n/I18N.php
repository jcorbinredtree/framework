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

/**
 * Internationalization SiteModule
 */
class I18N extends SiteModule
{

    /**
     * Holds a LangPack instance, the current language
     *
     * @var LangPack
     */
    private $pack = null;

    public function initialize()
    {
        require_once $this->moduleDir.'/LangPack.php';

        $this->site->addCallback('onPostConfig',   array($this, 'onPostConfig'));
        $this->site->addCallback('onRequestStart', array($this, 'onRequestStart'));
    }

    public function onPostConfig()
    {
        $this->requentKey  = $this->site->config->get('i18n.requestKey',  '_la');
        $this->cookieKey   = $this->site->config->get('i18n.cookieKey',   'lang');
        $this->defaultLang = $this->site->config->get('i18n.defaultLang', 'EN');
    }

    public function onRequestStart()
    {
        // TODO become more clueful:
        //   geo-ip
        //   user agent
        //   other?
        $reqval = Params::request($this->requestKey);
        if (isset($reqval)) {
            $lang = $reqval;
            setcookie($this->cookieKey, $lang, time() + EXPIRY_TIME);
        } else {
            $lang = Params::cookie($this->cookieKey);
        }

        if (! isset($lang)) {
            $lang = $this->defaultLang;
        }

        $this->set($lang);
    }

    /**
     * @return LangPack
     */
    public function getLangPack()
    {
        if (! isset($this->pack)) {
            throw new RuntimeException('no LangPack initialized');
        }
        return $this->pack;
    }

    /**
     * Sets the current language
     *
     * @param mixed $lang a language code, language id, or a LangPack object
     * @return boolean true on success
     */
    public function set($lang)
    {
        if (is_string($lang)) {
            $this->setLangPack($lang);
        } elseif (is_object($lang) && $lang instanceof LangPack) {
            $this->pack = $lang;
        } else {
            throw new InvalidArgumentException("invalid lang");
        }
    }

    /**
     * Filters the string/key through the current language pack.
     *
     * @param string $string the string/key you wish to retrieve a translation for
     * @return string the string/key filtered through the current language pack
     */
    public function string($string)
    {
        return I18N::$pack->get($string);
    }

    /**
     * Gets the specified language pack, and assigns it to
     * the static variable $pack.
     *
     * @param string $lang the language code, lowercase
     * @return LangPack the specified language pack, or
     * false if it could not be found
     */
    protected function setLangPack($lang)
    {
        $path = "$this->moduleDir/languages/".strtolower($lang).".php";
        if (! file_exists($path)) {
            throw new RuntimeException("no such lang pack, no path $path");
        }
        require_once $path;

        $class = 'I18N_'.strtoupper($lang);
        if (! class_exists($class)) {
            throw new RuntimeException("no such lang pack, no class $class");
        }
        if (! is_subclass_of($class, 'LangPack')) {
            throw new RuntimeException("$class is not a LangPack");
        }

        return $this->pack = new $class();
    }
}

?>
