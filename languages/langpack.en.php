<?php

/**
 * I18N_EN class definition
 *
 * PHP version 5
 *
 * LICENSE: This file is a part of the Red Tree Systems framework,
 * and is licensed royalty free to customers who have purchased
 * services from us. Please see http://www.redtreesystems.com for
 * details.
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
class I18N_EN implements LangPack
{
    private $translation = array();

    public function __construct()
    {
        $this->translation['_INTERNAL_ERROR'] = 'Internal Error';
    }

    public function get($string)
    {
        global $config;

        if (!isset($this->translation[$string])) {
            //$config->warn( "unknown string $string for " . $this->getLanguageName() . ' language pack' );
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
