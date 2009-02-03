<?php

class NotImplementedException extends Exception
{
    public function __construct()
    {
        parent::__construct('not documented');
    }
}

?>
