<?php
/**
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
 */

class Session
{
    public static $TimeKey = '__session_start_time';

    private function __construct()
    {
    }

    public static function configure(Site $site)
    {
        $lifetime = $site->config->get('session.expire', 0);
        $path = $site->config->get('session.path', $site->url);

        if ($path[strlen($path)-1] != '/') {
            $path .= '/';
        }

        session_set_cookie_params($lifetime, $path);
    }

    public static function start(Site $site)
    {
        self::configure($site);
        $r = session_start();
        if (! self::has(self::$TimeKey)) {
            self::set(self::$TimeKey, time());
        }

        $site->dispatchCallback('onSessionStart');

        return $r;
    }

    public static function check(Site $site)
    {
        $lifetime = $site->config->get('session.expire', 0);
        if (
            $lifetime > 0 &&
            time() - self::get(self::$TimeKey) >= $lifetime
        ) {
            self::end($site);
            self::start($site);
        }
    }

    public static function end(Site $site)
    {
        $site->dispatchCallback('onSessionEnd');
        $sname = session_name();
        if (array_key_exists($sname, $_COOKIE) && isset($_COOKIE[$sname])) {
            setcookie($sname, '', time()-42000, '/');
        }
        $_SESSION = array();
        return session_destroy();
    }

    public static function set($key, $data)
    {
        if (isset($data)) {
            $_SESSION[$key] = $data;
        } else {
            unset($_SESSION[$key]);
        }
    }

    public static function get($key, $default=null)
    {
        if (array_key_exists($key, $_SESSION) && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return $default;
        }
    }

    public static function has($key)
    {
        return array_key_exists($key, $_SESSION);
    }
}

# vim:set sw=4 ts=4 expandtab:
?>
