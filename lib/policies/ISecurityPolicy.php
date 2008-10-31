<?php

interface ISecurityPolicy
{
    public function login(IUser &$user);
}

?>