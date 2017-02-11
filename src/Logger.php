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
	 * @param array $logContext
	 *
	 * @return void
	 */
	public function log($level, $message, array $logContext = array())
	{
		$logContext = array_map(function($logContextRow) {
			if (is_object($logContextRow)) {
				$logContextRow = json_encode($logContextRow);
			}

			return str_replace("\n", ' ', $logContextRow);
		}, $logContext);

		$dateTime = new \DateTime();
		$contextStr = empty($logContext) ? '' : ' ["' . implode('","', $logContext) . '"]';

		$msg = '[' . $dateTime->format('Y-m-d H:i:s') . '] Callisto.' . $level . ': ' . $message . $contextStr . PHP_EOL;
		fwrite($this->handler, $msg, strlen($msg));
	}
}
