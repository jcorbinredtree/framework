<?php

/**
 * SitePageProvider definition
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

require_once 'lib/site/SitePage.php';

abstract class SitePageProvider
{
    const DECLINE  = null;
    const FAIL     = 2;
    const REDIRECT = 3;

    protected $site;

    /**
     * Creates a new page provider
     *
     * @param site Site the site
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
        $this->site->addCallback('onResolvePage', array($this, 'loadPage'));
    }

    /**
     * Called by Site::handle to ask the provider for a page
     *
     * @param url string, the url being served, relative to $site->url
     * @return mixed one of:
     *   DECLINE          - declines to serve the page
     *   FAIL             - the requested url should be error'd as not found
     *   REDIRECT         - redirect
     *   SItePage object  - the page to serve
     *   Any of the last 3 will stop the page resolution process with indicated
     *   result.
     *
     * @see redirect
     */
    abstract public function loadPage($url);

    /**
     * Adds a Location redirect header and returns the REDIRECT constant.
     *
     * The idea is that if a subclass wants to redirect the current page,
     * it'll do something like:
     *   return $this->redirect('some/page');
     *
     * @param to string the url, if not absolute, will be relative to $site->url
     *
     * @return REDIRECT
     */
    protected function redirect($to)
    {
        assert(is_string($to) && strlen($to) > 0);
        $to = $this->site->rel2abs($to);
        header("Location: $to");
        return self::REDIRECT;
    }
}

?>
