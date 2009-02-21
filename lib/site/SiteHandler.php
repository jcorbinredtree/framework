<?php

/**
 * SiteHandler definition
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

/**
 * Abstract base class for site request handlers
 */
abstract class SiteHandler
{
    /**
     * @var Site
     */
    protected $site;

    /**
     * Constructor
     *
     * @param site Site
     * @see Site::loadHandler
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Handles the request
     *
     * Subclasses must override this to handle requests
     *
     * Gets any additional arguments passed to Site::handle
     *
     * @see Site::handle
     */
    public function handle()
    // NOTE: this method is not declared abstract on purpose so that subclass
    // methods can declare whatever argumetns they may want; however this
    // method is effectively abstract, subclasses must override it
    {
        throw new RuntimeException(
            get_class($this)." doesn't implement handle"
        );
    }
}

?>
