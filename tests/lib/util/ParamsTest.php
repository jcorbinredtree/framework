<?php

require_once dirname(__FILE__) . "/../../bootstrap.php";

class ParamsTest extends FrameworkTestCase
{
	public function testGeneric()
	{
		$generic = array('existingkey' => 23);
		$this->assertTrue((23 == Params::generic($generic, 'existingkey')), 'finds key');
		$this->assertTrue(('default' == Params::generic($generic, 'badkey', 'default')), 'gets default');
	}
}

?>
