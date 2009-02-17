<?php

/**
 * PageTemplate class definition
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
 * @category     UI
 * @package      Utils
 * @author       Red Tree Systems, LLC <support@redtreesystems.com>
 * @copyright    2006 Red Tree Systems, LLC
 * @license      http://www.redtreesystems.com PROPRITERY
 * @version      2.0
 * @link         http://www.redtreesystems.com
 */

/**
 * PageTemplate
 *
 * This is a Template that represents a page. Whatever a page may be is up to
 * the template it self
 *
 * @category     UI
 * @package      Utils
 */
class PageTemplate extends Template
{
    /**
     * Constructor
     *
     * Creates a new page template
     *
     * @param page WebPage (optional) if given, the page that we're creating
     * a template for. If not given, WebPage::getCurrent() is used.
     */
    public function __construct(WebPage &$page=null, $template='common/xhtmlpage.xml')
    {
        // TODO page type (needs added yet) selects template
        parent::__construct($template);

        if (! isset($page)) {
            $page = WebPage::getCurrent();
        }
        $this->assign('page', $page);

        // Compatability with old sites
        if (is_a($page, 'LayoutDescription')) {
            $this->assign('layout', $page);
        }
    }

    /**
     * Process all page buffers right before the page template is rendered, this
     * finalizes all page content at essentially the last possible point on the
     * way out.
     */
    protected function renderSetup($args)
    {
        parent::renderSetup($args);
        $this->page->processBUffers();
    }

    /**
     * Obviously this method makes no sense for a PageTemplate
     */
    public function renderToPage($area, WebPage &$page=null)
    {
        throw new RuntimeException("Won't render a page template to a page");
    }
}

?>
