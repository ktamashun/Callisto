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
class Filter extends Stream
{
	/**
	 * Streaming endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '/1.1/statuses/filter.json';

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
	protected $httpMethod = 'POST';


	/**
	 * @return array
	 */
	protected function getParams() : array
	{
		$return = [];

		foreach ($this->requestParameters as $filter) {
			$return[$filter->getKey()] = $filter->getValue();
		}

		return $return;
	}

	/**
	 * Sets the filters to use in the request.
	 *
	 * @param RequestParameter[] $requestParameters
	 * @return $this Fluent interface.
	 */
	public function setRequestParameters($requestParameters)
	{
		$this->requestParameters = $requestParameters;
		return $this;
	}
}
