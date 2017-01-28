<?php
/**
 * @author: kovacs.tamas.hun@gmail.com
 */

namespace Callisto\Stream;

use Callisto\Stream;


/**
 * Class Stream
 * @package Callisto
 */
class Filter extends Stream
{
	/**
	 * Streaming endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '/1.1/statuses/filter.json';

	/**
	 * Http method to use when connecting to the streaming API.
	 *
	 * @var string
	 */
	protected $httpMethod = 'POST';
}
