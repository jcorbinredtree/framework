<?php

class Session
{
    public static function set($key, &$data)
    {
        $_SESSION[$key] = $data;
    }
    
    public static function get($key)
    {
        return Params::session($key, null);
    }
    
    public static function end()
    {
        $_SESSION = array();
        return session_destroy();
    }
}

?>