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

require_once 'lib/i18n/I18N.php';

class Main
{
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
     * Restores a previously saved request
     *
     * @return void
     */
    public static function restoreRequest()
    {
        if ($request = Application::popSavedRequest()) {
            global $current;

            if (Site::Site()->isDebugMode()) {
                Site::getLog()->debug("restoring saved request: " . print_r($request, true));
            }

            $_GET = $request->get;
            $_POST = $request->post;
            $_REQUEST = $request->request;

            $current->component = $request->component;
        }
    }

    /**
     * Set the language
     *
     * @return void
     */
    public static function setLanguage()
    {
        // TODO revamp this into site/page
        global $current;

        $req_key = '_la';
        $cookie_key = 'lang';
        // set language from
        // A.) Cookies
        // B.) _lang_id request
        // C.) default
        $langId = (int) Params::cookie($cookie_key, 0);

        if (Params::request($req_key)) {
            $langId = (int) Params::request($req_key);
            setcookie($cookie_key, $langId, time() + EXPIRY_TIME);
        }

        if ($langId) {
            I18N::set($langId);
        } else {
            I18N::set('EN');
        }
    }
}

?>
