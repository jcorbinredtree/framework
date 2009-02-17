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

class DefaultExceptionTheme extends Theme {
    public function onDisplay(WebPage &$page) {
        $template = $this->createPageTemplate($page);
        if (is_a($page, 'LayoutDescription') && is_a($page->content, 'Exception')) {
            $template->assign('exception', $page->content);
        } else {
            $template->assign('exception', $page->getData('exception'));
        }
        $this->write($template->fetch('view/container.xml'));
    }
}

?>