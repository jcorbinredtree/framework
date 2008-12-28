<?php

require_once dirname(__FILE__) . '/Config.php';

$type = (isset($APP) && $APP) ? $APP : 'web';

switch ($type) {
    case 'web-lite':
        require_once dirname(__FILE__) . '/web-lite.php';
        break;        
    case 'web':
        require_once dirname(__FILE__) . '/web.php';
        break;
    case 'test':
        require_once dirname(__FILE__) . '/tests.php';
        break;
    case 'cli':
        require_once dirname(__FILE__) . '/cli.php';
        break;
}

?>