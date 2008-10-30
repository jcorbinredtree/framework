<?php
/**
 * User Settings class definition
 *
 * PHP version 5
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
 * @category     Components
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2007 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      1.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Sets up the UserSettings component
 *
 * @package        Components
 */
class UserSettings extends Component
{
    const ACTION_VIEW = 1;
    const ACTION_EDIT = 2;
    const ACTION_CHANGE_PASSWORD = 3;
    
    public function onRegisterActions() 
    {
        /*
         * view
         */
        {
            $action = new ActionDescription();
            $action->id = UserSettings::ACTION_VIEW;
            $action->handler = 'onView';
            $action->requiresUser = true;
            $this->registerAction($action);            
        }
        
        /*
         * edit
         */
        {
            $action = new ActionDescription();
            $action->id = UserSettings::ACTION_EDIT;
            $action->handler = 'onEdit';
            $action->requiresUser = true;
            $this->registerAction($action);            
        }

        /*
         * change password
         */
        {
            $action = new ActionDescription();
            $action->id = UserSettings::ACTION_CHANGE_PASSWORD;
            $action->handler = 'onChangePassword';
            $action->requiresUser = true;
            $this->registerAction($action);            
        }        
    }
    
    public function onView($stage)
    {
        global $config, $current;

        $this->title = 'Account Information';
        $template = new Template();
        
        $template->assign('definition', User::$definition);
        $template->assign('user', $current->user);
        $this->write($template->fetch('view/view.xml'));
    }
    
    public function onEdit($stage)
    {
        global $current;
        
        $this->title = 'Update Account Information';
        $current->user->merge($_REQUEST);
        
        switch($stage) {
            default:
            case Stage::VIEW:
                $template = new Template();
                
                $template->assign('definition', User::$definition);
                $template->assign('user', $current->user);
                $this->write($template->fetch('view/edit.xml'));                
                break;
            case Stage::VALIDATE:
                return $current->user->validate();
            case Stage::PERFORM:
                if (!$current->user->update()) {
                    return false;
                }
                
                Application::Forward(Component::getActionURI('UserSettings', UserSettings::ACTION_VIEW, Stage::VIEW, array('-textalize' => true)));
        }
        
        return true;
    }
    
    public function onChangePassword($stage)
    {
        global $current;
        
        $password = Params::REQUEST('password');
        
        switch($stage) {
            default:
            case Stage::VIEW:
                $template = new Template();
                
                $template->assign('definition', User::$definition);
                $template->assign('user', $current->user);
                $this->write($template->fetch('view/changepassword.xml'));                
                break;
            case Stage::VALIDATE:
                if (!$password) {
                    $current->addWarning("You must enter a password");
                    return false;
                }
                
                if (strlen($password) < 6) {
                    $current->addWarning("Please enter a password of at least six characters");
                    return false;                    
                }
                
                break;
            case Stage::PERFORM:
                if (!$current->user->updatePassword($password)) {
                    return false;
                }
                
                Application::Forward(Component::getActionURI('UserSettings', UserSettings::ACTION_VIEW, Stage::VIEW, array('-textalize' => true)));
        }
        
        return true;        
    }
}

?>
