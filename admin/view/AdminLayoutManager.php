<?php

class AdminLayoutManager extends BufferedObject
{    
    public function display(LayoutDescription &$layout)
    {
        $template = new Template();
        $template->assign('layout', $layout);

        $this->write($template->fetch('layouts/container.xml'));
    }
}

?>