<?php

abstract class LifeCycleAdapter implements ILifeCycle
{        
    /**
     * Called when an exception is generated from the system
     * 
     * @param Exception $ex the exception
     * @return void
     */    
    public function onException(Exception &$ex)
    {
        global $config;

        $config->info('onException');
    }

    /**
     * Called when the application first starts
     *
     * @return void
     */
    public function onInitialize()
    {
        global $config;
        
        $config->info("onInitialize");
    }
    
    /**
     * Invoked to parse GET and POST info out of the URL.
     * Returning a true value stops the processor and leaves 
     * the parsing up to you. Tread lightly!
     *
     * @return boolean true if you handled the operation
     */
    public function onURLRewrite()
    {
        global $config;
        
        $config->info("onURLRewrite");
        
        return false;
    }

    /**
     * The request has started, and basic initializations have been performed
     *
     * @return void
     */
    public function onRequestStart()
    {
        global $config;
        
        $config->info("onRequestStart");
    }
    
    /**
     * Called when an action is invoked. Returning a boolean value here will prevent the action
     * from being called, and pass your value through to the framework.
     *
     * @param ActionProvider $provider
     * @param ActionDescription $description
     * @return boolean to handle the action, null to ignore
     */
    public function onAction(ActionProvider &$provider, ActionDescription &$description)
    {
        global $config;
        
        $config->info("onAction");
        return null;
    }    

    /**
     * Called to get items for the top navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetTopNavigation()
    {
        global $config;
        
        $config->info("onGetXXXNavigation");
        return array();
    }    
    
    /**
     * Called to get items for the right navigation
     *
     * @return array of NavigatorItem objects
     */    
    public function onGetRightNavigation()
    {
        global $config;
        
        $config->info("onGetXXXNavigation");
        return array();
    }    
    
    /**
     * Called to get items for the bottom navigation
     *
     * @return array of NavigatorItem objects
     */    
    public function onGetBottomNavigation()
    {
        global $config;
        
        $config->info("onGetXXXNavigation");
        return array();
    }    
    
    /**
     * Called to get items for the left navigation
     *
     * @return array of NavigatorItem objects
     */
    public function onGetLeftNavigation()
    {
        global $config;
        
        $config->info("onGetXXXNavigation");
        return array();
    }    

    /**
     * Called to get items for the top modules
     *
     * @return array of NavigatorItem objects
     */    
    public function onGetTopModules()
    {
        global $config;
        
        $config->info("onGetXXXModules");
        return array();
    }    
    
    /**
     * Called to get items for the right modules
     *
     * @return array of NavigatorItem objects
     */    
    public function onGetRightModules()
    {
        global $config;
        
        $config->info("onGetXXXModules");
        return array();
    }    
    
    /**
     * Called to get items for the bottom modules
     *
     * @return array of NavigatorItem objects
     */
    public function onGetBottomModules()
    {
        global $config;
        
        $config->info("onGetXXXModules");
        return array();
    }    
    
    /**
     * Called to get items for the left modules
     *
     * @return array of NavigatorItem objects
     */    
    public function onGetLeftModules()
    {
        global $config;
        
        $config->info("onGetXXXModules");
        return array();
    }    

    /**
     * Invoked before anything is written to the browser, but after everything has
     * been collected in the LayoutDescription object
     *
     * @param LayoutDescription $layout
     * @return void
     */
    public function onPreRender(LayoutDescription &$layout)
    {
        global $config;
        
        $config->info("onPreRender");
    }    

    /**
     * Called after the contents have been written to the browser.
     *
     * @return void
     */
    public function onPostRender()
    {
        global $config;
        
        $config->info("onPostRender");
    }              
}

?>