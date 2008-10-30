<?php

/**
 * Login class definition
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
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * Performs login routines
 *
 * @package      Components
 */
class Login extends Component
{
    const CAPTCHA_KEY = '_login_signup_captcha';
    
    /**
     * @var LoginModel
     */
    private $model;

    public function onRegisterActions()
    {
        $this->model = new LoginModel();
        
        /*
         * login
         */
        {
            $action = new ActionDescription();
            $action->id = 'login';
            $action->handler = 'onLogin';
            $this->registerAction($action);            
        }
        
        /*
         * captcha
         */
        $this->registerAction(new ActionDescription(array(
            'id' => 'captcha',
            'handler' => 'onCaptcha'            
        )));
        
        /*
         * logout
         */
        {
            $action = new ActionDescription();
            $action->id = 'logout';
            $action->handler = 'onLogout';
            $this->registerAction($action);            
        }
        
        /*
         * signup
         */
        {
            $action = new ActionDescription();
            $action->id = 'signup';
            $action->handler = 'onSignup';
            $this->registerAction($action);            
        }
        
        /*
         * forgot password
         */
        {
            $action = new ActionDescription();
            $action->id = 'forgot-password';
            $action->handler = 'onForgotPassword';
            $this->registerAction($action);            
        }
    }
    
    /*
     * actions
     */

    public function onLogin($stage)
    {
        global $config,$current;
        
        $this->title = I18N::String('Login');
        
        switch ($stage) {
            case Stage::VIEW:
                $template = new Template();

                $template->assign('user', Params::post('user'));
                $this->write($template->fetch('view/loginform.xml'));
                break;
            case Stage::VALIDATE:            
            case Stage::PERFORM:
                if ($uid = User::login(Params::post('user'), Params::post('pass'))) {
                    $config->info("User #$uid has successfully logged in");
                    $_SESSION['user_id'] = $uid;
                    $_SESSION['time'] = time();
        
                    Application::forward();
                }
        
                $config->info("Failed login attempt with user '" . Params::post('user') . "'");
                $current->addWarning(I18N::String('_UNKNOWN_CREDENTIALS'));
                return false;
        }       
        
        return true;
    }

    public function onSignup($stage)
    {
        global $config, $current;
                  
        if (!Config::ALLOW_SIGNUP) {
            $current->addWarning("permission denied");
            return;
        }        
        
        $this->title = I18N::String('Signup');

        $user = User::From($_REQUEST);   
        $captcha = Params::request('captcha');   

        switch($stage) {
            case Stage::VIEW:
                $template = new Template();

                $template->assign('definition', User::$definition);
                $template->assign('user', $user);
                $this->write($template->fetch('view/signupform.xml'));                
                break;
            case Stage::VALIDATE:
                if (Params::session(Login::CAPTCHA_KEY) != $captcha) {
                    $current->addWarning("Could not verify the pass phrase");
                    return false;
                }
                
                return $user->validate();
            case Stage::PERFORM:
                unset($_SESSION[Login::CAPTCHA_KEY]);
                
                $res = $user->create();
                if ($res === User::ERROR_EMAIL_EXISTS) {
                    $current->addWarning(I18N::String('_USER_EMAIL_EXISTS'));
                    return false;
                }
                
                if ($res === User::ERROR_USERNAME_EXISTS) {
                    $current->addWarning(I18N::String('_USER_NAME_EXISTS'));
                    return false;
                }
                
                if (!$res) {
                    return false;
                }
        
                if (!User::registerUserPassword($user, "$config->company registration", "%s,<br>Your password is %s")) {
                    return false;
                }
        
                $current->addNotice(I18N::String('_SIGNUP_THANKS'));
                Application::Forward(Component::GetActionURI('Login', Login::ACTION_LOGIN, Stage::VIEW, array('-textalize' => true)));
                break;
        }
        
        return true;
    }

    public function onForgotPassword($stage)
    {
        global $config, $current;
        
        if (!Config::ALLOW_FORGOT_PASSWORD) {
            $current->addWarning("permission denied");
            return;
        }
        
        $this->title = I18N::String('Forgot Password');
        
        switch($stage) {
            case Stage::VIEW:
                $template = new Template();
                $this->write($template->fetch('view/forgotpassword.xml'));                
                break;
            case Stage::VALIDATE:
                $email = trim(Params::POST('email'));
        
                if (!$email) {
                    $current->addWarning(I18N::String('_ENTER_EMAIL'));
                    return false;
                }
                
                if (!Email::IsValid($email)) {
                    $current->addWarning(I18N::String('_EMAIL_INVALID'));
                    return false;
                }
                                
                break;
            case Stage::PERFORM:
                $email = trim(Params::POST('email'));
                $userId = $this->model->userIdFromEmail($email);
                $user = new User();
                if ($user->fetch($userId)) {                
                    User::registerUserPassword($user, 'Your new password', "<p>Your login credentials, as per your request</p><p>%s<br>%s</p>");
                }
                
                $current->addNotice("Your password has been emailed to you");
                Application::forward();
                break;                
        }
        
        return true;
    }
    
    public function onCaptcha($stage)
    {
        Captcha::Display(Login::CAPTCHA_KEY);
    }

    public function onLogout($stage)
    {
        global $current, $config;

        $current->user = null;
        $_SESSION = array();

        $current->addNotice("You have logged out");        
        
        Application::forward($config->absUri);
    }
}

?>
