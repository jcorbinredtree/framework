<?php

class DMSTemplate extends PHPSTLTemplate 
{
    public function __construct()
    {
        global $config;
        
        Compiler::setCompileDirectory($config->compileDir);
        Compiler::setCompilerClass('DMSCompiler');
    }
}

?>