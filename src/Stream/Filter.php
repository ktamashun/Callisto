<?php
/**
 * @author: kovacs.tamas.hun@gmail.com
 */

namespace Callisto\Stream;

use Callisto\BaseFilter;
use Callisto\BaseStream;


/**
 * Class Stream
 * @package Callisto
 */
abstract class Filter extends BaseStream
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
	 * @var \Callisto\BaseFilter[]
	 */
	protected $filters = [];

	/**
	 * Http method to use when connecting to the streaming API.
	 *
	 * @var string
	 */
	protected $httpMethod = 'POST';


	/**
	 * @return array
	 */
	protected function getParams()
	{
		$return = [];

		foreach ($this->filters as $filter) {
			$return[$filter->getKey()] = $filter->getValue();
		}

		return $return;
	}

	/**
	 * Sets the filters to use in the request.
	 *
	 * @param BaseFilter[] $filters
	 * @return $this Fluent interface.
	 */
	public function setFilters($filters)
	{
		$this->filters = $filters;
		return $this;
	}
}
