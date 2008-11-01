<?php

require_once dirname(__FILE__) . '/Config.php';

if (isset($RUNTEST) && $RUNTEST) {
    require_once dirname(__FILE__) . '/tests.php';    
}
else {
    require_once dirname(__FILE__) . '/web.php';    
}

?>