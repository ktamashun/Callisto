<?php
/**
 * @author: kovacs.tamas.hun@gmail.com
 */

namespace Callisto\Stream;

use Callisto\RequestParameter;
use Callisto\Stream;


/**
 * Class Stream
 * @package Callisto
 */
class Firehose extends Stream
{
	/**
	 * Streaming endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '/1.1/statuses/firehose.json';

	/**
	 * Parameters to use to filter the statuses.
	 *
	 * @var \Callisto\RequestParameter[]
	 */
	protected $requestParameters = [];

	/**
	 * Http method to use when connecting to the streaming API.
	 *
	 * @var string
	 */
	protected $httpMethod = 'GET';


	/**
	 * @return array
	 */
	protected function getParams() : array
	{
		return [];
	}
}
