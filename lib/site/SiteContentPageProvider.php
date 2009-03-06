<?php

/**
 * SiteContentPageProvider definition
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
 * @category     Site
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

require_once 'lib/site/SitePageProvider.php';
require_once 'lib/ui/HTMLPage.php';

/**
 * Resolves urls to a template resource "pageContent:url"
 */
class SiteContentPageProvider extends SitePageProvider
{
    private static $loadingPage = null;

    /**
     * Returns the page being currently loaded, this is so that template
     * providers can poke at it
     *
     * @return HTMLPage
     */
    public static function getLoadingPage()
    {
        return self::$loadingPage;
    }

    /**
     * Loads a content page, currently limited to being "only" an HTMLPage
     * @see SitePageProvider::loadPage
     */
    public function loadPage($url)
    {
        try {
            self::$loadingPage = $page = new HTMLPage(
                $this->site, null, "pageContent:$url"
            );
            self::$loadingPage = null;
            return $page;
        } catch (PHPSTLNoSuchResource $ex) {
            self::$loadingPage = null;
            return SitePageProvider::DECLINE;
        } catch (Exception $e) {
            self::$loadingPage = null;
            throw $e;
        }
    }
}

?>
