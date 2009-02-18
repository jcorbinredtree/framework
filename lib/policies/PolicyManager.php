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

class PolicyManager implements ILocationPolicy, ILinkPolicy, ISecurityPolicy, IThemePolicy
{
    private static $instance = null;

    /**
     * The location policy
     *
     * @var ILocationPolicy
     */
    private $locationPolicy = null;

    /**
     * The link policy
     *
     * @var ILinkPolicy
     */
    private $linkPolicy = null;

    /**
     * The security policy
     *
     * @var ISecurityPolicy
     */
    private $securityPolicy = null;

    /**
     * The theme policy
     *
     * @var IThemePolicy
     */
    private $themePolicy = null;

    public function get($policy)
    {
        switch ($policy) {
            case 'location':
                return $this->locationPolicy;
            case 'link':
                return $this->linkPolicy;
            case 'security':
                return $this->securityPolicy;
            case 'theme':
                return $this->themePolicy;
            default:
                return null;
        }
    }

    public function set($policy, $handler)
    {
        switch ($policy) {
            case 'link':
                if (!($handler instanceof ILinkPolicy)) {
                    throw new InvalidArgumentException("handler for $policy does not adhere to ILinkPolicy");
                }

                $this->linkPolicy = $handler;
                break;
            case 'location':
                if (!($handler instanceof ILocationPolicy)) {
                    throw new InvalidArgumentException("handler for $policy does not adhere to ILocationPolicy");
                }

                $this->locationPolicy = $handler;
                break;
            case 'security':
                if (!($handler instanceof ISecurityPolicy )) {
                    throw new InvalidArgumentException("handler for $policy does not adhere to ISecurityPolicy");
                }

                $this->securityPolicy = $handler;
                break;
            case 'theme':
                if (!($handler instanceof IThemePolicy )) {
                    throw new InvalidArgumentException("handler for $policy does not adhere to IThemePolicy");
                }

                $this->themePolicy = $handler;
                break;
            default:
                throw new InvalidArgumentException("unknown policy $policy");
        }
    }

    public function getTemplatesDir()
    {
        return $this->locationPolicy->getTemplatesDir();
    }

    public function getLogsDir()
    {
        return $this->locationPolicy->getLogsDir();
    }

    public function getCacheDir()
    {
        return $this->locationPolicy->getCacheDir();
    }

    public function logs()
    {
        $this->locationPolicy->logs();
    }

    /**
     * @see ILinkPolicy::parse()
     *
     */
    public function parse()
    {
        $this->linkPolicy->parse();
    }

    /**
     * @see ILinkPolicy::getActionURI()
     * @return string
     */
    public function getActionURI($component, $action, $options=array(), $stage=Stage::VIEW)
    {
        return $this->linkPolicy->getActionURI($component, $action, $options, $stage);
    }

    public function getLoginUrl()
    {
        return $this->securityPolicy->getLoginUrl();
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
        return $this->securityPolicy->login($un, $pass);
    }

    /**
     * @see ISecurityPolicy::logout()
     *
     */
    public function logout()
    {
        $this->securityPolicy->logout();
    }

    /**
     * @see ISecurityPolicy::restore()
     *
     * @return IUser
     */
    public function restore()
    {
        return $this->securityPolicy->restore();
    }

    /**
     * @see IThemePolicy::getTheme()
     *
     * @return Theme
     */
    public function getTheme()
    {
        return $this->themePolicy->getTheme();
    }

    /**
     * @see IThemePolicy::getExceptionTheme()
     *
     * @return Theme
     */
    public function getExceptionTheme()
    {
        return $this->themePolicy->getExceptionTheme();
    }

    /**
     * Gets a PolicyManager instance
     *
     * @return PolicyManager
     */
    public static function getInstance()
    {
        if (!PolicyManager::$instance) {
            PolicyManager::$instance = new PolicyManager();
        }

        return PolicyManager::$instance;
    }

    private function __construct()
    {
        $this->locationPolicy = new DefaultLocationPolicy();
        $this->linkPolicy = new DefaultLinkPolicy();
        $this->securityPolicy = new DefaultSecurityPolicy();
        $this->themePolicy = new DefaultThemePolicy();
    }
}

?>
