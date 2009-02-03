<?php

/**
 * DefaultThemePolicy
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
 * @package      Policies
 * @category     UI
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2008 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      3.0
 * @link         http://framework.redtreesystems.com
 */

/**
 * This class represents the default theme loading behavior
 *
 * @package      Policies
 * @category     UI
 */

class DefaultThemePolicy implements IThemePolicy
{
    /**
     * set theme from A.) Cookies, B.) _theme_id request, C.) default
     *
     * @return Theme The theme to load
     * @see IThemePolicy::getTheme()
     */
    public function getTheme()
    {
        global $config;

        $themeId = (int) Params::cookie(AppConstants::THEME_COOKIE, 0);

        if (Params::request(AppConstants::THEME_KEY)) {
            $themeId = (int) Params::request(AppConstants::THEME_KEY);
            setcookie(AppConstants::THEME_COOKIE, $themeId, time() + Config::COOKIE_LIFETIME);
        }

        if ($themeId) {
            return Theme::load($themeId);
        }

        return Theme::load($config->getDefaultTheme());
    }

    /**
     * Called to get the exception theme
     *
     * @return Theme The theme to load
     * @see IThemePolicy::getExceptionTheme
     */
    public function getExceptionTheme()
    {
        global $config;

        return Theme::load($config->getDefaultExceptionTheme());
    }
}

?>
