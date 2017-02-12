<?php
/**
 * Created by PhpStorm.
 * User: ktamashun <kovacs.tamas.hun@gmail.com>
 * Date: 2017. 02. 12.
 * Time: 18:49
 */

namespace Callisto;

/**
 * We need to mock the fwrite function.
 *
 * @param $handler
 * @param $msg
 * @param $length
 * @return bool
 */
function fwrite($handler, $msg, $length)
{
	LoggerTest::$loggedMessage = $msg;
	return true;
}

use PHPUnit\Framework\TestCase;

class LoggerTest extends TestCase
{
	public static $loggedMessage;

	public function testLog()
	{
		$handler = fopen('php://stdout', 'w');
		$logger = new Logger($handler);
		$logger->log('info', 'Test message', ['apple' => 1]);

		$this->assertTrue(false !== strpos(self::$loggedMessage, 'Callisto.info: Test message ["1"]'));
	}

	public function testLogObject()
	{
		$handler = fopen('php://stdout', 'w');
		$logger = new Logger($handler);
		$logger->log('info', 'Test message', [new \stdClass()]);

		$this->assertTrue(false !== strpos(self::$loggedMessage, 'Callisto.info: Test message ["{}"]'));
	}
}