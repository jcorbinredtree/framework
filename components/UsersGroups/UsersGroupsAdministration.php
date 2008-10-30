<?php

class UsersGroupsAdministration extends Administration
{
    public function onRegisterActions()
    {
        $provider =& ProviderDescription::getInstance("Users & Groups");
        $provider->weight = 5;
        $provider->icon = 'group.png';

        {
            $item = new ActionDescription();
            $item->id = 'list-users';
            $item->handler = array($this, 'onListUsers');
            $item->label = 'List Users';
            $item->icon = 'group.png';
            $item->requireGroups = array('Administrators');
            $item->requiresUser = true;

            $this->registerAction($item);
            $provider->default = array($this, $item);
            $provider->addAction($this, $item);
        }
    }
    
    public function onListUsers($stage)
    {
    	$this->write('<p>not implemented</p>');
    }
}

?>