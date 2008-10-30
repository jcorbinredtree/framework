<?php

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
     * populates the current ticket
     * 
     * @return  void
     */
    public static function populateCurrent()
    {
        global $current, $config;
        
        $current = null;
        if (Params::session(AppConstants::LAST_CURRENT_KEY)) {
            $current = Application::popSavedCurrent();
        }
        else {
            /**
             * The current variable is the third of three global variables
             * in the application. This variable holds the current state
             * of the application such as the physical path, and messages
             * between the application and user.
             * 
             * @global Current $current
             * @see Current;
             */
            $current = new Current();
        }
        
        $current->id = Params::request('id', 0);            
        $current->setSecureRequest(Params::request(AppConstants::SECURE_KEY));
        $componentClass = (Params::request(AppConstants::COMPONENT_KEY) 
                              ? Params::request(AppConstants::COMPONENT_KEY)
                              : $config->getDefaultComponent());            
        $current->component = Component::load($componentClass);            
        if (!$current->component) {
            $config->error("Unknown component $componentClass");                
            throw new Exception("Unknown component $componentClass");
        }
        
        $actionId = (Params::request(AppConstants::ACTION_KEY, null)
                               ? Params::request(AppConstants::ACTION_KEY, null)
                               : $config->getDefaultAction());
        $current->action = $current->component->getAction($actionId);            
        if (!$current->action) {
            $message = "Unknown action $actionId";
            $config->error($message);
            throw new Exception($message);
        }
        
        $current->stage = Params::request(AppConstants::STAGE_KEY, Stage::VIEW);        
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
        && (((time() - Params::session('time')) >= $config->sessionExpireTime))) 
        {
            $_SESSION = array();
            Application::saveRequest();
        
            $current->addNotice("Your session has timed-out, please log in again");
        
            Application::login($current);
        }
        elseif ($config->getSessionExpireTime()) {
            $_SESSION['time'] = time();
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
        
        $uid = Session::get('user_id');
        if (!$uid) {
            return;
        }
    
        $current->user = new User();
        $current->user->fetch($uid);
    
        /*
         * do we have a valid user?
         */
        if ((!$current->user) || (!$current->user->id)) {
            Application::saveRequest();
    
            $config->warn("User #$uid not found");
            $current->addWarning("User #$uid unknown or blocked. Please login again.");
    
            $_SESSION = array();
    
            Application::login($current);
        }
        else {
            $_SESSION['time'] = time();
        }
        
        if ((!$current->component->allows($current->action))                        /* current action denied */
            || (!$current->user && Params::request(AppConstants::FORCE_LOGIN_KEY))) /* request to log in (but not user) */
        {
            Application::saveRequest();
        
            $config->info('User not found, loading Application::login');
        
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
            $config->debug("restoring saved request: " . print_r($request, true));
            
            if (($request->component)
                && ($obj = call_user_func(array($request->component, 'load'), $request->component)))
            {
                $_GET = $request->get;
                $_POST = $request->post;
                $_REQUEST = $request->request;
    
                $current->component = $obj;
                $current->action = $current->component->getAction($request->action);
                $current->stage = $request->stage;
            }
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
                
        /*
         * set language from A.) Cookies, B.) _lang_id request, C.) default
         * set theme from A.) Cookies, B.) _theme_id request, C.) default
         */
        {
            $langId = (int) Params::cookie(AppConstants::LANGUAGE_COOKIE, 0);
            $themeId = (int) Params::cookie(AppConstants::THEME_COOKIE, 0);
        
            if (Params::request(AppConstants::LANGUAGE_KEY)) {
                $langId = (int) Params::request(AppConstants::LANGUAGE_KEY);
                setcookie(LANGUAGE_COOKIE, $langId, time() + EXPIRY_TIME);
            }
        
            if ($langId) {
                I18N::set($langId);
            }
            else {
                I18N::set('EN');
            }
        
            if (Params::request(AppConstants::THEME_KEY)) {
                $themeId = (int) Params::request(AppConstants::THEME_KEY);
                setcookie(AppConstants::THEME_COOKIE, $themeId, time() + Config::COOKIE_LIFETIME);
            }
        
            if ($themeId) {
                $current->theme = Theme::Load($themeId);
            }
            else {
                $current->theme = Theme::Load($config->getDefaultTheme());
            }
        }
        
        if (!$current->theme) {
            $message = 'Theme "' . Params::request(AppConstants::THEME_KEY) . '" not found';
            $config->error($message);
            throw new Exception($message);
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
        
        if (Params::request(AppConstants::NO_HTML_KEY)) {
            Application::performAction($current->component, $current->action, $current->stage);
            
            $config->debug('output: ' . $current->component->getBuffer());
            
            $current->component->flush();
            return;    
        }
        
        $layout = new LayoutDescription();    
    
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
        switch ($current->stage) {
            default:
            case Stage::VIEW:
                Application::performAction($current->component, $current->action, $current->stage);
                break;
            case Stage::VALIDATE:
                if (!Application::call($current->component, $current->action, Stage::VALIDATE)) {
                    Application::performAction($current->component, $current->action, Stage::VIEW);            
                    break;                
                }            
            case Stage::PERFORM:
                if (!Application::call($current->component, $current->action, Stage::PERFORM)) {
                    Application::performAction($current->component, $current->action, Stage::VIEW);            
                }
                
                break;
        }
    
        /*
         * populate the LayoutDescription
         */
        {
            $combinedItems = array_merge($layout->getTopNavigation(), $layout->getLeftNavigation(), 
                                         $layout->getRightNavigation(), $layout->getBottomNavigation());
                                                                      
            $vars = get_object_vars($current->component);
            foreach ($vars as $prop => $val) {
                if (property_exists($layout, $prop)) {
                    $layout->$prop = $val;
                }
            }
            
            $layout->isPopup = Params::request(AppConstants::POPUP_KEY, false);
            $layout->component = $current->component;
            $layout->searchWord = Params::request(AppConstants::KEYWORD_KEY);
            $layout->isHomePage = ($current->component->getClass() == $config->getDefaultComponent()) 
                                  && ($current->action->id == $config->getDefaultAction());
    
            $layout->styleSheets = $current->theme->getStyleSheets();
           
            if ($layout->currentItem =& NavigatorItem::find($current->id, $combinedItems)) {
                $layout->currentItem->isCurrent = true;
                $layout->topLevelItem =& $layout->currentItem->getTopLevelParent();
            }
            
            /*
             * modules
             */
            {
                foreach (LifeCycleManager::onGetLeftModules() as $module) {
                    Application::performModule($module, Module::POSITION_LEFT);
                    $layout->addLeftModule($module);
                }
    
                foreach (LifeCycleManager::onGetTopModules() as $module) {
                    Application::performModule($module, Module::POSITION_TOP);
                    $layout->addTopModule($module);
                }
                
                foreach (LifeCycleManager::onGetRightModules() as $module) {
                    Application::performModule($module, Module::POSITION_RIGHT);
                    $layout->addRightModule($module);
                }
    
                foreach (LifeCycleManager::onGetBottomModules() as $module) {
                    Application::performModule($module, Module::POSITION_BOTTOM);
                    $layout->addBottomModule($module);
                }            
            }
        }
        
        //print_r($layout);exit(0);
        $current->layout =& $layout;
    
        LifeCycleManager::onPreRender($current->layout);    
        {        
            $layout->content = $current->component->getBuffer();        
            if (!$config->isDebugMode()) {
                $current->theme->addFilter(new WhitespaceFilter());
            }
        
            /*
             * set the current path to the theme location & display
             */
            Application::setPath($current->theme->getPath()); 
            $current->theme->onDisplay($layout);     
            $current->theme->flush();
        }        
        LifeCycleManager::onPostRender();                
    }
}

?>