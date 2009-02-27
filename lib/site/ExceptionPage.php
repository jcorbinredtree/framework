<?php

/**
 * ExceptionPage definition
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

/**
 * ExceptionPage is the page used to display unhandled exceptions
 *
 * It's a basic no frills html page.
 *
 * Note: this should NOT be an HTMLPage, the idea is that this page is used when
 * something goes wrong, and so should have as few moving parts as possible
 *
 * @package Ui
 */
class ExceptionPage extends SitePage
{
    /**
     * Constructor
     *
     * Creates a new HTMLPage.
     *
     * While this is publically accessible for flexibility, this should be
     * sparingly used; you likely meant to call the static method Current.
     *
     * @param site Site
     * @param ex Exception
     * @param oldPage SitePage
     * @see Current
     */
    public function __construct(Site $site, Exception $ex, $oldPage=null)
    {
        parent::__construct($site, 'text/html', 'page/exception.xml');

        if (! isset($oldPage)) {
            if (isset($this->site->page) && $this->site->page !== $this) {
                $oldPage = $this->site->page;
            }
        }
        $this->setData('oldPage', $oldPage);
        $this->setData('exception', $ex);

        $this->headers->setContentTypeCharset('utf-8');
        $this->headers->setStatus(500, 'Unhandled Exception');
    }
}

?>
