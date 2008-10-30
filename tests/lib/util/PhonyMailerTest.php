<?php

require_once dirname(__FILE__) . "/../../bootstrap.php";

class PhonyMailerTest extends FrameworkTestCase
{
	public function testSend()
	{
		global $config;
		
		$mailer = $config->getMailer();
		$mailer->Subject = 'Unit Test';
		$mailer->Body = 'Unit Test';
		$mailer->AddAddress('webmaster@redtreesystems.com');

		$this->assertTrue($mailer->Send(), 'sends mail');
	}
}

?>