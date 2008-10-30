<?php

class AdministrationDriver extends ActionProvider implements IDriver
{
    const ACTION_AVAILABLE_ACTIONS = 1;
    const LOGOFF = 2;
    
    public static function getActionURI($component, $action, $stage=Stage::VIEW, $options=array())
    {
        return array();
    }
    
    public function onRegisterActions()
    {
        $action = new ActionDescription();
        $action->handler = 'onAvailableActions';
        $action->id = AdministrationDriver::ACTION_AVAILABLE_ACTIONS;
        $this->registerAction($action);

        $this->registerAction(array(
            'id' => AdministrationDriver::LOGOFF,
            'handler' => 'onLogoff'            
        ));
    }
    
    public function onLogoff($stage)
    {
        global $config;                
        
        $_SESSION = array();
        session_write_close();
        header("Location: $config->absUri/admin/");
        exit(0);
    }
    
    public function onAvailableActions($stage)
    {
        global $current;
        
        $currentProvider = null;
        $target = Params::request('provider');
        foreach ($current->layout->providerDescriptions as $provider) {
            if ($provider->name == $target) {
                $currentProvider =& $provider;
                break; 
            }
        }
        
        if ($currentProvider == null) {
            $this->write('unknown provider');
            return;
        }
        
        $template = new Template();
        $template->assign('provider', $currentProvider);
        $this->write($template->fetch('view/actions.xml'));
    }
    
    /**
     * Implements the perform
     *
     * @param ActionDescription $action
     * @param int $stage
     * @return boolean
     */
    public function perform(ActionDescription &$action, $stage) 
    {
        global $config;
        
        $class = $this->getClass();
        $path = Application::setPath("$config->absPath/admin/drivers/$class/");
       
        $handler = (is_string($action->handler) ? array($this, $action->handler) : $action->handler); 
        $returnValue = call_user_func($handler, $stage);

        Application::setPath($path);

        return $returnValue;        
    }        
}

?>