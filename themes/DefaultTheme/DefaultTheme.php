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

class DefaultTheme extends Theme {
    public function onDisplay(WebPage &$page) {
        global $current;

        $template = $this->createPageTemplate($page);

        // TODO revamp this, process the inner template here rather than telling
        // the container what template to process

        if (is_a($page, 'LayoutDescription')) {
            if ($page->isHomePage) {
                $template->assign('template', 'homelayout.xml');
                $template->assign('css', 'homelayout.css');
            }
            else {
                $template->assign('template', 'innerlayout.xml');
                $template->assign('css', 'innerlayout.css');
            }

            $this->write($template->fetch('view/layouts/container.xml'));
        } else {
            throw new RuntimeException(
                'Proper page processing unimplemented as yet'
            );
        }
    }
}

?>
