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
}
