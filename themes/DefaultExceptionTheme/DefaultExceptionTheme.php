<?php

class DefaultExceptionTheme extends Theme {
    public function onDisplay(LayoutDescription &$layout) {
        $template = new Template();
        $template->assign('layout', $layout);
        $this->write($template->fetch('view/container.xml'));
    }
}

?>
