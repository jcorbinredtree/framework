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

class Main
{
    /**
     * Starts a session.
     *
     * @return void
     */
    public static function startSession()
    {
        global $config;

        $path = $config->absUriPath;
        $path = (preg_match('/\/$/', $path) ? $path : "$path/");

        session_set_cookie_params($config->getSessionExpireTime(), $path);
        session_start();
    }

    /**
     * Parses the request and populates the $_GET and $_REQUEST arrays. The order of precedence is as follows:
     *
     * 1.) An ILifeCycle implementation handling onURLRewrite()
     * 2.) A matching key in $config->getUrlMappings()
     * 3.) The ILinkPolicy::parse() method
     *
     * @return void
     */
    public static function parseRequest()
    {
        global $config;

        $handledUrl = LifeCycleManager::onURLRewrite();
        if (!$handledUrl) {
            $mappings = $config->getUrlMappings();
            $url = preg_replace('|^' . $config->absUriPath . '[/]?|i', '', Params::server('REQUEST_URI'));
            if ($url) {
                foreach ($mappings as $key => $map) {
                    if ($key == $url) {
                        $_REQUEST[AppConstants::COMPONENT_KEY] = $_GET[AppConstants::COMPONENT_KEY] = $map[0];
                        $_REQUEST[AppConstants::ACTION_KEY] = $_GET[AppConstants::ACTION_KEY] = $map[1];
                        if (count($map) > 2) {
                            $sets = explode(',', 'null,' . $map[2]);
                            foreach ($sets as $set) {
                                if ($set == 'null') {
                                    continue;
                                }

                                $args = explode('=', $set);

                                $_REQUEST[$args[0]] = $_GET[$args[0]] = $args[1];
                            }
                        }

                        if (count($map) > 3) {
                            $_REQUEST[AppConstants::STAGE_KEY] = $_GET[AppConstants::STAGE_KEY] = $map[3];
                        }

                        $handledUrl = true;
                        break;
                    }
                }
            }

            if (!$handledUrl) {
                $policy = PolicyManager::getInstance();
                $policy->parse();
            }
        }
    }

    /**
     * Initialize the current component
     *
     * @return void
     */
    public static function loadCurrent()
    {
        global $current;

        if (Session::get(AppConstants::LAST_CURRENT_KEY)) {
            $current = Application::popSavedCurrent();
        }
    }

    /**
     * populates the current ticket
     *
     * @return  void
     */
    public static function populateCurrent()
    {
        global $current, $config;

        $current->setSecureRequest(Params::request(AppConstants::SECURE_KEY));
        $componentClass = (Params::request(AppConstants::COMPONENT_KEY)
                              ? Params::request(AppConstants::COMPONENT_KEY)
                              : $config->getDefaultComponent());
        $current->component = Component::getInstance($componentClass);
        if (! $current->component) {
            throw new RuntimeException("Unknown component $componentClass");
        }
    }

    /**
     * Requires the -secure requests be secured via https by forwarding the request
     * to the https equivalent
     *
     * @return void
     */
    public static function secureRequest()
    {
        global $current;

        if (($current->isSecureRequest()) && (!Params::server('HTTPS'))) {
            $uri = $current->getCurrentRequest(array('-textalize' => true));

            Application::Forward($uri);
        }
    }

    /**
     * Enforces session timeouts
     *
     * @return void
     */
    public static function sessionTimeout()
    {
        global $config, $current;

        if ($config->getSessionExpireTime() && Params::session('user_id')
            && (((time() - Session::get(AppConstants::TIME_KEY)) >= $config->sessionExpireTime)))
        {
            $_SESSION = array();
            Application::saveRequest();

            $current->addNotice("Your session has timed-out, please log in again");

            Application::login($current);
        }
        elseif ($config->getSessionExpireTime()) {
            Session::set(AppConstants::TIME_KEY, time());
        }
    }

    /**
     * Loads a user from the session or request
     *
     * @return void
     */
    public static function loadUser()
    {
        global $config, $current;

        $policy = PolicyManager::getInstance();
        $user = $policy->restore();
        if (!$user) {
            return;
        }

        $current->user =& $user;
        Session::set(AppConstants::TIME_KEY, time());
    }

    /**
     * Defines the user access rules
     *
     * @return void
     */
    public static function accessRules()
    {
        global $current, $config;

        $denied = false;

        /* current action denied */
        if (!$current->component->allows($current->component->action)) {
            $config->info('permission denied by component access rules, loading Application::login');
            $denied = true;
        }

        /* request to log in (but not user) */
        if (!$current->user && Params::request(AppConstants::FORCE_LOGIN_KEY))
        {
            $config->info('permission denied by force-login rule, loading Application::login');
            $denied = true;
        }

        if ($denied) {
            Application::login($current);
        }
    }

    /**
     * Restores a previously saved request
     *
     * @return void
     */
    public static function restoreRequest()
    {
        global $current, $config;

        if (!$current->user || !Session::get(AppConstants::SAVED_REQUEST_KEY)) {
            return;
        }

        if ($request = Application::popSavedRequest()) {
            if ($config->isDebugMode()) {
                $config->debug("restoring saved request: " . print_r($request, true));
            }

            $_GET = $request->get;
            $_POST = $request->post;
            $_REQUEST = $request->request;

            $current->component = $request->component;
        }
    }

    /**
     * Set the language & theme
     *
     * @return void
     */
    public static function setLanguageAndTheme()
    {
        global $config, $current;

        { // set language from A.) Cookies, B.) _lang_id request, C.) default
            // TODO honor language in page
            $langId = (int) Params::cookie(AppConstants::LANGUAGE_COOKIE, 0);

            if (Params::request(AppConstants::LANGUAGE_KEY)) {
                $langId = (int) Params::request(AppConstants::LANGUAGE_KEY);
                setcookie(LANGUAGE_COOKIE, $langId, time() + EXPIRY_TIME);
            }

            if ($langId) {
                I18N::set($langId);
            } else {
                I18N::set('EN');
            }
        }
    }

    /**
     * Render the current setup to the user
     *
     * @return void
     */
    public static function render()
    {
        global $config, $current;

        if (! $config->targetVersionOver(3, 0, 76)) {
            WebPage::setCurrent(new LayoutDescription());
        }
        $page = WebPage::getCurrent();

        /*
         * +====================+
         * |  VIEW (cacheable)  <-------------^------------<
         * +====================+             |            |
         *                                 NO |            |
         * +====================+     +==============+     |
         * |      VALIDATE      |-----> Return True? |     |
         * +====================+     +==============+     |
         *                                    |            |
         * +====================+             |            |
         * |       PERFORM      <-------------v YES        |
         * +=========|==========+                          |
         *           |                                     |
         * +=========v==========+                          |
         * |    Return False?   |--------------------------^ YES
         * +====================+
         */
        switch ($current->component->stage) {
            default:
            case Stage::VIEW:
                Application::performAction($current->component, $current->component->action, $current->component->stage);
                break;
            case Stage::VALIDATE:
                if (!Application::call($current->component, $current->component->action, Stage::VALIDATE)) {
                    Application::performAction($current->component, $current->component->action, Stage::VIEW);
                    break;
                }
            case Stage::PERFORM:
                if (!Application::call($current->component, $current->component->action, Stage::PERFORM)) {
                    Application::performAction($current->component, $current->component->action, Stage::VIEW);
                }

                break;
        }


        $page->addToBuffer('content', $current->component);

        $page->getTheme()->render();
    }
}

?>
