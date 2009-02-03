<?php

class DefaultSecurityPolicy implements ISecurityPolicy
{

    /**
     * @see ISecurityPolicy::getLoginUrl()
     */
    public function getLoginUrl()
    {
        global $config;

        return "$config->absUri/login";
    }

    /**
     * @see ISecurityPolicy::login()
     *
     * @param string $un
     * @param string $pass
     * @return boolean
     */
    public function login($un, $pass)
    {
        if (($un == 'admin') && ($pass == 'admin')) {
            Session::set('__uid', 1);
            return true;
        }

        return false;
    }

    /**
     * @see ISecurityPolicy::logout()
     *
     */
    public function logout()
    {

    }

    /**
     * @see ISecurityPolicy::restore()
     *
     * @return IUser
     */
    public function restore()
    {
        global $config, $current;

        $uid = Session::get('__uid');
        if (!$uid) {
            return;
        }

        return new DefaultUser();
    }
}

?>
