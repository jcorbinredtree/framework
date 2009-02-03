<?php

class DefaultTheme extends Theme {
    public function onDisplay(LayoutDescription &$layout) {
        global $current;

        $template = new Template();
        $template->assign('layout', $layout);

        if ($layout->isHomePage) {
            $template->assign('template', 'homelayout.xml');
            $template->assign('css', 'homelayout.css');
        }
        else {
            $template->assign('template', 'innerlayout.xml');
            $template->assign('css', 'innerlayout.css');
        }

        $this->write($template->fetch('view/layouts/container.xml'));
    }
}

?>
