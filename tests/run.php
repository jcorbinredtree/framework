<?php

require_once "bootstrap.php";

function onFound($file)
{
	print "$file\n";
	if (preg_match('|Test[.]php$|', $file)) {
		include $file;
	}	
}

File::find('onFound', "$config->absPath/tests");

?>