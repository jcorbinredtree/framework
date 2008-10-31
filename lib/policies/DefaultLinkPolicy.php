<?php

class DefaultLinkPolicy implements ILinkPolicy
{
    /**
     * @see ILinkPolicy::parse()
     */
    public function parse()
    {
        global $config;
        
        $url = preg_replace('|^' . $config->absUriPath . '[/]?|i', '', Params::server('REQUEST_URI'));        
        if (!$url) {
            return;
        }

        $pageUrl = preg_replace('|[?](?:.*)$|', '', $url);
        $gets = explode('/', $pageUrl);        
        
        if (count($gets) % 2) {
            header("HTTP/1.1 400 Bad Request");
            header("Content-Type: text/plain");
            
            die("I'm sorry, I didn't understand that request. Please try again.\n");
        }
        
        for ($i = 0; $i < count($gets); $i += 2) {
            list($name, $value) = array($gets[$i], $gets[$i + 1]);
                        
            $_GET[$name] = $_REQUEST[$name] = urldecode($value);
        }

        if (preg_match('/[?]/', $url)) {
            $requestUrl = preg_replace('|^(?:.+)[?]|i', '&', $url);
            $gets = explode('&', $requestUrl);

            for ($i = 1; $i < count($gets); $i++) {
                list($name, $value) = explode('=', $gets[$i]);
                            
                $_GET[$name] = $_REQUEST[$name] = urldecode($value);
            }
        }    
    }
    
    /**
     * Returns text in href form suitable for linking to other actions within the framework.
     * 
     * @see ILinkPolicy::getActionURI
     * @access public
     * @param string a component class name
     * @param string $action the action id you want to link to
     * @param array $options an associative array of parameters to pass to the action. You may set
     * -textalize to true if you are using the text directly (ie not in an href). This
     * option will be removed from the final link, but does not do encoding transformations
     * such as & => &amp;.
     * 
     * -popup indicates a popup window
     * 
     * -secure indicates a secure (https) link
     * 
     * -no-html indicates to disregard the theme, and go directly to the action (for binary and such)
     * @param int $stage the stage you want to link to, default Stage::VIEW
     * @return string text to use in an href upon success; null upon failure
     */
    public function getActionURI($component, $action, $options=array(), $stage=Stage::VIEW)
    {
        global $config;
        
        $amp = array_key_exists('-textalize', $options) ? '&' : '&amp;';

        $component = urlencode($component);

        $link = $config->absUri;

        if ($config->sefLinks) {
            $link .= "/" . AppConstants::COMPONENT_KEY . "/$component";
        }
        else {
            $link .= "/?" . AppConstants::COMPONENT_KEY . "=$component";
        }

        if ($action) {
            if ($config->sefLinks) {
                $link .= '/' . AppConstants::ACTION_KEY . "/$action";
            }
            else {
                $link .= $amp . AppConstants::ACTION_KEY . "=$action";
            }
        }
        
        if ($stage) {
            if ($config->sefLinks) {
                $link .= '/' . AppConstants::STAGE_KEY . "/$stage";
            }
            else {
                $link .= $amp . AppConstants::STAGE_KEY . "=$stage";
            }
        }        
        
        /*
         * if we're a popup, other links should be popups
         */
        if (!array_key_exists('-popup', $options) && Params::request(AppConstants::POPUP_KEY)) {
            $options[AppConstants::POPUP_KEY] = 1;
        }

        foreach ($options as $kw => $val) {
            $skip = false;
            
            if ($kw[0] == '-') {
                switch ($kw) {
                    case '-textalize':
                        $skip = true;
                        break;
                    case '-popup':
                        $kw = AppConstants::POPUP_KEY;
                        break;
                    case '-no-html':
                        $kw = AppConstants::NO_HTML_KEY;
                        break;
                    case '-secure':
                        $kw = AppConstants::SECURE_KEY;
                        break;
                }                
            }
            
            if ($skip) {
                continue;
            }

            if ($val === null) {
                continue;
            }
            
            if ($kw == AppConstants::SECURE_KEY) {
                if ($val) {
                    $link = preg_replace('/^http[:]/i', 'https:', $link);
                }
                else {
                    $link = preg_replace('/^https[:]/i', 'http:', $link);
                }
            }

            $kw = urlencode($kw);

            if ($config->sefLinks) {
                $link .= "/$kw/$val";
            }
            else {
                $link .= "${amp}$kw=$val";
            }
        }

        return $link;        
    }
}

?>