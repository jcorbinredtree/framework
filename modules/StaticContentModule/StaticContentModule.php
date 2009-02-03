<?php

class StaticContentModule extends Module
{
    public function onDisplay($position)
    {
        $this->viewTemplate('view/content.xml');
    }

    public function isCacheable()
    {
        return true;
    }

    public function useCache($time)
    {
        global $config;

        return (filemtime("$config->absPath/modules/StaticContentModule/view/content.xml") <= $time);
    }
}

?>
