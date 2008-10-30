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
}

?>