<?php

/**
 * PageProvider definition
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
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2009 Red Tree Systems, LLC
 * @license      MPL 1.1
 * @version      2.0
 * @link         http://framework.redtreesystems.com
 */

abstract class PageProvider
{
    const DECLINE  = null;
    const FAIL     = 2;

    protected $pagesys;

    /**
     * Creates a new page provider
     *
     * @param PageSystem $pagesys the page system module
     */
    public function __construct(PageSystem $pagesys)
    {
        $this->pagesys = $pagesys;
        $this->pagesys->addProvider($this);
    }

    /**
     * Called to ask the provider to resolve an url to a Page object
     *
     * @param url string, the url being served, relative to Site::$url
     * @return mixed one of:
     *   DECLINE      - declines to serve the page
     *   FAIL         - the requested url should be error'd as not found
     *   Page object  - the page to serve
     *   Any of the last 3 will stop the page resolution process with indicated
     *   result.
     */
    abstract public function resolve($url);
}

?>
