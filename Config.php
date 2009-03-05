<?php

/**
 * Config class definition
 *
 * PHP version 5.3+
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
 * @category     Application
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Contains configuration information
 *
 * There should be only one instance of this class throughout the platform,
 * but is not made into a singleton class for flexibility reasons.
 *
 * @category   Config
 * @package    Application
 */
class Config
{
    /**
     * Framework version handling
     *
     * A version string is of the form Major.minor.micro
     *   3.0.0 or 3.1.99
     */
    static public $FrameworkVersion = "3.0.76";
    static private $fwVersion = null;

    private $targetVersion = 3.00074;

    static public function makeVersionFromString($string)
    {
        $matches = array();
        if (preg_match('/(\d+)\.(\d+)(?:\.(\d+))?/', $string, $matches)) {
            $matches = array_slice($matches, 1);
            $major = (int) $matches[0];
            $minor = (int) $matches[1];
            if (count($matches) > 2) {
                $micro = (int) $matches[2];
            } else {
                $micro = null;
            }
            return self::makeVersion($major, $minor, $micro);
        } else {
            throw new InvalidArgumentException("Invalid version $major");
        }
    }

    static public function makeVersion($major, $minor, $micro=null)
    {
        $ver = $major + $minor/100;
        if (isset($micro)) {
            $ver += $micro/100000;
        }

        // A float like a.bbccc such as 3.00090 or 3.01005
        return $ver;
    }

    /**
     * This is the version of the framework that the site is targeted against.
     *
     * This defaults to "3.0" since that's when versioning was introduced.
     *
     * If the target version doesn't contain a micro version, it will default to 74.
     *
     * This is due to the versioning convention such that:
     *   The development version of "3.2.0" will be versioned like "3.1.80"
     *   or so with a micro version over 75.
     *
     * The end result of this is that a site that says it targets "3.0" is
     * effectively saying "I'm okay with 3.0.0-3.0.74", which can be
     * restated "all stable 3.0.x releases".
     */
    public function setTargetVersion($version)
    {
        $this->targetVersion = self::makeVersionFromString($version);
    }

    /**
     * Tests whether the site's targeted framework version is past a given
     * version
     *
     * @param major int
     * @param minor int
     * @return boolean
     */
    public function targetVersionOver($major, $minor, $micro)
    {
        $ver = self::makeVersion($major, $minor, $micro);
        return $this->targetVersion >= $ver;
    }

    /**
     * Tests whether the framework version is over a given version
     *
     * @param major int
     * @param minor int
     * @param micro int optional
     * @return boolean
     */
    public function frameworkVersionOver($major, $minor, $micro=null)
    {
        if (! isset(self::$fwVersion)) {
            self::$fwVersion = self::makeVersionFromString(self::$FrameworkVersion);
        }
        $ver = self::makeVersion($major, $minor, $micro);
        return self::$fwVersion >= $ver;
    }

    /**
     * This is the database connection information, in a MDB2 DSN format
     *
     * @var string
     */
    private $dsn = 'mysql://root:@localhost/framework';

    /**
     * Databse options, as specified by PDO
     *
     * @var string
     */
    private $dbOptions = null;

    /**
     * Specifies the lifetime (timeout) of a session in seconds.
     * Set to 0 to mean until the user logs out or the the browser is closed.
     *
     * @var int
     */
    private $sessionExpireTime = 0;

    private $templateOptions = array(
        // Extra places to look for templates, entries relative to SiteLoader::$Base
        // 'include_path' => "path,path,..." -or- array('path','path',...)

        // Where templates are cached, default is determined by SiteLayout
        //   which defaults to SITE/writable/cache/templates
        // 'diskcache_directory' => '...'
    );

    public function addTemplateOptions($o)
    {
        assert(is_array($o));
        if (
            array_key_exists('include_path', $o) &&
            array_key_exists('include_path', $this->templateOptions)
        ) {
            $this->templateOptions['include_path'] = array_merge(
                $this->templateOptions['include_path'],
                $o['include_path']
            );
            unset($o['include_path']);
        }
        $this->templateOptions = array_merge($this->templateOptions, $o);
    }

    public function getTemplateOptions()
    {
        return $this->templateOptions;
    }

    /**
     * Specifies options to the mailer. The keys in this hash directly map to the
     * phpmailer properties.
     *
     * @var array
     */
    private $mailerOptions = array(
        'From' => 'somewhere@example.com',
        'FromName' => 'Red Tree Framework',
        'Host' => 'localhost',
        'Mailer' => 'smtp',
        'Auth' => false,
        'Username' => '',
        'Password' => ''
    );

    /**
     * Holds the user configuration values
     *
     * @var array
     */
    private $userConfig = array();

    /**
     * Sets a configuration value
     *
     * @param $key
     * @param $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->userConfig[$key] = $value;
    }

    /**
     * Gets a user set configuration value
     *
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->userConfig[$key];
    }

    /**
     * @param array $mailerOptions
     */
    public function setMailerOptions ($mailerOptions)
    {
        $this->mailerOptions = $mailerOptions;
    }

    /**
     * @param array $mailerOptions
     */
    public function addMailerOptions ($mailerOptions)
    {
        $this->mailerOptions = array_merge($this->mailerOptions, $mailerOptions);
    }
    /**
     *
     * Gets db options
     *
     * @return array
     */
    public function getDatabaseOptions()
    {
        return $this->dbOptions;
    }

    /**
     * Set database info
     * @param $dsn the dsn
     * @return string
     */
    public function setDatabaseInfo($dsn)
    {
        $this->dsn = $dsn;
    }


    /**
     * Gets the session expiration time
     *
     * @return int
     */
    public function getSessionExpireTime()
    {
        return $this->sessionExpireTime;
    }

    /**
     * Sets the session expiration time
     *
     * @return void
     */
    public function setSessionExpireTime($n)
    {
        $this->sessionExpireTime = $n;
    }

    /**
     * Gets the mailer options that comprise a new mailer object
     *
     * @return array
     */
    public function getMailerOptions()
    {
        return $this->mailerOptions;
    }

    private $site;

    /**
     * Constructor; Sets up a lot of vars & such
     *
     * @access public
     * @return Config a new instance
     */
    public function __construct($site=null)
    {
        if (! isset($site)) {
            $site = Site::Site();
        }
        $this->site = $site;
    }

    /**
     * Gets a PHPMailer instance, configured for this
     * environment.
     *
     * @access public
     * @return PHPMailer a PHPMailer instance, already configured
     */
    public function getMailer()
    {
        $mailerPath = SiteLoader::$Base."/extensions/phpmailer";
        include_once "$mailerPath/class.phpmailer.php";

        $mail = ($this->test ? new PhonyMailer() : new PHPMailer());
        $mail->PluginDir = "$mailerPath/";

        foreach ($this->mailerOptions as $key => $val) {
            $mail->$key = $this->mailerOptions[$key];
        }

        return $mail;
    }
}

?>
