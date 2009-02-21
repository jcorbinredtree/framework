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

require_once 'Log.php';

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
     * This is the test connection information, in a MDB2 DSN format
     *
     * @var string
     */
    private $testDsn = 'mysql://root:@localhost/framework';

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

        // Where templates are cached, default is determined by the policy
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
     * Determines if we are in debug mode or not
     *
     * @access protected
     * @var boolean
     */
    private $debug = false;

    /**
     * Determines if we are in test mode or not
     *
     * @access protected
     * @var boolean
     */
    private $test = false;

    /**
     * The logger object
     *
     * @access protected
     * @var Log
     */
    private $log = false;

    /**
     * True if we are running as cli
     *
     * @var boolean
     */
    private $cli = false;

    /**
     * The framework version
     *
     * @var string
     */
    private $version = "3.0";

    /**
     * Holds a refernence to the url mappings
     *
     * @var array
     */
    private $urlMappings = array();

    /**
     * The default or "home page" component
     *
     * @var String
     */
    private $defaultComponent = 'DefaultComponent';

    /**
     * The default or "home page" action
     *
     * @var String
     */
    private $defaultAction = 'home';

    /**
     * The default theme
     *
     * @var String
     */
    private $defaultTheme = 'DefaultTheme';

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
     * @return String
     */
    public function getDefaultTheme()
    {
        return $this->defaultTheme;
    }

    /**
     * @param String $defaultTheme
     */
    public function setDefaultTheme ($defaultTheme)
    {
        $this->defaultTheme = $defaultTheme;
    }
    /**
     * @return String
     */
    public function getDefaultAction ()
    {
        return $this->defaultAction;
    }

    /**
     * @return String
     */
    public function getDefaultComponent ()
    {
        return $this->defaultComponent;
    }

    /**
     * @param String $defaultAction
     */
    public function setDefaultAction ($defaultAction)
    {
        $this->defaultAction = $defaultAction;
    }

    /**
     * @param String $defaultComponent
     */
    public function setDefaultComponent ($defaultComponent)
    {
        $this->defaultComponent = $defaultComponent;
    }

    /**
     * @return Log
     */
    public function getLog ()
    {
        return $this->log;
    }

    /**
     * @param Log $log
     */
    public function setLog (Log &$log)
    {
        $this->log =& $log;
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
     * Gets the current dsn
     *
     * @return string
     */
    public function getDatabaseInfo()
    {
        return ($this->test ? $this->testDsn : $this->dsn);
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
     * Set test info
     * @param $dsn the dsn
     * @return string
     */
    public function setDatabaseTestInfo($dsn)
    {
        $this->testDsn = $dsn;
    }

    /**
     * Sets the test mode.
     *
     * @param boolean $val
     * @return void
     */
    public function setTestMode($val)
    {
        if (!$val) {
            $this->test = false;
            return;
        }

        $this->test = true;
        $policy = PolicyManager::getInstance();
        $policy->logs();
    }

    /**
     * Determines the test mode
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->test;
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
     * Determines if we are in debug mode or not
     *
     * @return boolean true if we're in debug mode
     */
    public function isDebugMode()
    {
        return $this->debug;
    }

    /**
     * Sets if we are in debug mode or not
     *
     * @param boolean $val true if we're in debug mode
     * @return void
     */
    public function setDebugMode($val)
    {
        $this->debug = $val;

        $policy = PolicyManager::getInstance();
        $policy->logs();
    }

    /**
     * Gets the current framework version
     *
     * @return string
     */
    public function getVersion()
    {
        $this->version;
    }

    /**
     * Returns an associtive array of url mappings. The map must be in format:
     * page => array(<component>, <action>, [<args>], [<stage>])
     *
     * <args> should be given as <name>=<value>&<name>=<value>, such that a complete map would
     * look like:
     *
     * array(
     *  'other-file.html' => array('Documents', Document::ACTION_GET, 'id=1&name=file', Stage::VIEW)
     * );
     *
     * @return array of mappings
     */
    public function getUrlMappings()
    {
        return $this->urlMappings;
    }

    /**
     * Add a mapping in the format:
     * array(
     *  'other-file.html' => array('Documents', Document::ACTION_GET, 'id=1&name=file', Stage::VIEW)
     * );
     *
     * @see Config#getUrlMappings()
     * @param string $key
     * @param array $mapping
     * @return void
     */
    public function addUrlMapping($url, $mapping)
    {
        $this->urlMappings[$url] = $mapping;
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

    /**
     * The absolute uri, such as http://place.com/full/path/to/app.
     * This value is calculated in the constructor.
     *
     * @access public
     * @var string
     */
    public $absUri = null;

    /**
     * DEPRECATED
     * use SiteLoader::$Base instead
     *
     * @access public
     * @var string
     */
    public $absPath = null;

    /**
     * DEPRECATED
     * use SiteLoader::$FrameworkPath instead
     *
     * @var string
     */
    public $fwAbsPath = null;

    /**
     * The absolute uri path, such as /full/path/to/app. This value is
     * calculated as dirname( $_SERVER[ 'PHP_SELF' ] ), but could be
     * wrong if you're using SEF links.
     *
     * @access public
     * @var string
     */
    public $absUriPath = null;

    /**
     * Constructor; Sets up a lot of vars & such
     *
     * @access public
     * @return Config a new instance
     */
    public function __construct()
    {
        $this->cli = ('cli' === php_sapi_name());
        if ( $this->cli ) {
            $_SERVER = array(
                'SERVER_NAME' => 'cli',
                'SERVER_PORT' => 80,
                'PHP_SELF' => '/'
            );
        }

        $this->fwAbsPath = SiteLoader::$FrameworkPath;
        $this->absPath = SiteLoader::$Base;

        $this->absUriPath = dirname($_SERVER['PHP_SELF']);

        $this->log =& Log::singleton('null');
    }

    /**
     * Initializes the config (mostly just sets the absUri though)
     */
    public function initalize()
    {
        $proto = (
            isset($_SERVER['SERVER_PORT']) &&
            $_SERVER['SERVER_PORT'] == 443
        ) ? 'https' : 'http';
        $this->absUri = sprintf('%s://%s%s',
            $proto,
            $_SERVER['SERVER_NAME'],
            ($this->absUriPath == '/' ? '' : $this->absUriPath)
        );
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

    /**
     * Writes a debug message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function debug($message, $frame=2)
    {
        if (PEAR_LOG_DEBUG <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_DEBUG);
        }
    }

    /**
     * Writes a message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function log($message, $frame=2)
    {
        $this->info($message, $frame+1);
    }

    /**
     * Writes an info message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function info($message, $frame=2)
    {
        if (PEAR_LOG_INFO <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_INFO);
        }
    }

    /**
     * Writes a notice message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function notice($message, $frame=2)
    {
        if (PEAR_LOG_NOTICE <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_NOTICE);
        }
    }

    /**
     * Writes a warning message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function warn($message, $frame=2)
    {
        if (PEAR_LOG_WARNING <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_WARNING);
        }
    }

    /**
     * Writes an error message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function error($message, $frame=2)
    {
        if (PEAR_LOG_ERR <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_ERR);
        }
    }

    /**
     * Writes an alert message to the log
     * @TODO: should this send an email?
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function alert($message, $frame=2)
    {
        if (PEAR_LOG_ALERT <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_ALERT);
        }
    }

    /**
     * Writes a fatal message to the log
     *
     * @param string $message the message you want
     * to write to the log
     * @return void
     */
    public function fatal($message, $frame=2)
    {
        if (PEAR_LOG_EMERG <= $this->log->getMask()) {
            $this->log->log($this->callingFrame($frame) . $this->binSafe($message), PEAR_LOG_EMERG);
        }
    }

    /**
     * Returns the calling frames $frame frame, formatted properly.
     *
     * @return string a string representing the callers frame
     */
    public function callingFrame($frame=2)
    {
        global $__start;

        $trace = debug_backtrace();

        $function = (isset($trace[$frame]['function']) ? $trace[$frame]['function'] : '');
        $line     = (isset($trace[$frame]['line'])     ? $trace[$frame]['line']     : '');
        $type     = (isset($trace[$frame]['type'])     ? $trace[$frame]['type']     : '');

        $out = sprintf('[%.4f] %s', (microtime(true) - $__start), $function);

        if ($line) {
            $out .= " ($line): ";
        }

        $out .= "${type}";

        return $out;
    }

    private function binSafe(&$message)
    {
        $out = array();
        $msgLen = strlen($message);
        $len = ($this->isDebugMode() ? $msgLen : (($msgLen >= 1024) ? 1024 : $msgLen));
        for ($i = 0; $i < $len; $i++) {
            $chr = ord($message[$i]);
            $out[$i] = (($chr != 10) && ((($chr < 32) || ($chr > 127))) ? '?' : $message[$i]);
        }

        return implode('', $out);
    }

    /**
     * If debugging is on, files a complaint through php's error handling about
     * use of a deprecated interface with optional advice on what to do instead.
     *
     * @param old string describing the old interface
     * @param new string describing the new interface (optional)
     *
     * @return void
     */
    public function deprecatedComplain($old, $new=null, $from=null, $at=null)
    {
        if (! $this->debug) {
            return;
        }

        if (! isset($from) || ! isset($at)) {
            $trace = debug_backtrace();
            if (! isset($from)) {
                $from = $trace[2]['class'].$trace[2]['type'].$trace[2]['function'];
            }
            if (! isset($at)) {
                $at = $trace[1]['file'].':'.$trace[1]['line'];
            }
        }

        $mess = "Call to deprecated $old from $from at $at";
        if (isset($new)) {
            $mess .= ", use $new instead";
        }

        global $current;
        if (isset($current)) {
            $current->addNotice($mess);
        } else {
            trigger_error($mess);
        }
    }
}

?>
