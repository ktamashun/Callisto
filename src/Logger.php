<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 28.
 * Time: 0:24
 */

namespace Callisto;


use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{
	/**
	 * @var resource
	 */
	protected $handler;


	/**
	 * Logger constructor.
	 * @param resource $handler
	 */
	public function __construct($handler)
	{
		$this->handler = $handler;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return void
	 */
	public function log($level, $message, array $context = array())
	{
		$context = array_map(function($row) {
			if (is_object($row)) {
				$row = json_encode($row);
			}

			return str_replace("\n", ' ', $row);
		}, $context);

		$dateTime = new \DateTime();
		$contextStr = empty($context) ? '' : ' ["' . implode('","', $context) . '"]';

		$msg = '[' . $dateTime->format('Y-m-d H:i:s') . '] Callisto.' . $level . ': ' . $message . $contextStr . PHP_EOL;
		fwrite($this->handler, $msg, strlen($msg));
	}
}
