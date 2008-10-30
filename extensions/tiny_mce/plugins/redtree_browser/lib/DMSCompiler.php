<?php

class DMSCompiler extends Compiler
{
    /**
     * Constructor; writes global variables to be available to templates
     *
     * @param string $file
     */
    public function __construct($file='')
    {
        parent::__construct($file);
        $this->write('<?php global $config; ?>');
    }
}

?>