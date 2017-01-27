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
		$contextStr = ' ["' . implode('","', $context) . ']"';

		echo '[' . $dateTime->format('Y-m-d H:i:s') . '] Callisto.' . $level . ': ' . $message . $contextStr . PHP_EOL;
	}
}
