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

        $component = urlencode($component);

        $link = $config->absUri;

        /*
         * The Action Rule: if the action linked to requires ssl, then set the link to https, otherwise to http
         */
        {
            $c = call_user_func(array($component, 'getInstance'), $component);
            $a = $c->getAction($action);

            if (!$c) {
                throw new InvalidArgumentException("unknown component $component or action $action");
            }

            if (!$a) {
                throw new InvalidArgumentException("unknown action $component.$action");
            }

            if (!($a instanceof ActionDescription)) {
                throw new InvalidArgumentException("bad action $component.$action");
            }

            if ($a->requiresSSL) {
                $link = $this->replaceProto($link, true);
            }
            else {
                $link = $this->replaceProto($link, false);
            }
        }

        $link .= "/" . AppConstants::COMPONENT_KEY . "/$component";

        if ($action) {
            $link .= '/' . AppConstants::ACTION_KEY . "/$action";
        }

        if ($stage) {
            $link .= '/' . AppConstants::STAGE_KEY . "/$stage";
        }

        /*
         * if we're a popup, other links should be popups
         */
        if (!array_key_exists('-popup', $options) && Params::request(AppConstants::POPUP_KEY)) {
            $options[AppConstants::POPUP_KEY] = 1;
        }

        $qs = '';
        foreach ($options as $kw => $val) {
            if ($kw[0] == '-') {
                switch ($kw) {
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

            if ($val === null) {
                continue;
            }

            /*
             * -secure takes precendence over The Action Rule
             */
            if ($kw == AppConstants::SECURE_KEY) {
                if ($val) {
                    $link = $this->replaceProto($link, true);
                }
                else {
                    $link = $this->replaceProto($link, false);
                }
            }

            $kw  = urlencode($kw);
            $val = urlencode($val);

            /*
             * the query string rule: apache will freak out if %2F is part of the url
             */
            if (false !== strpos($val, '%2F')) {
                $qs .= "&$kw=$val";
            }
            else {
                $link .= "/$kw/$val";
            }
        }

        if ($qs) {
            $qs[0] = '?';
        }

        return $link . $qs;
    }

    private function replaceProto($link, $https)
    {
        if ($https) {
            return preg_replace('/^http[:]/i', 'https:', $link);
        }

        return preg_replace('/^https[:]/i', 'http:', $link);
    }
}

?>
