<?php
/**
 * @author: Tamás Kovács <kovacs.tamas.hun@gmail.com>
 */

namespace Callisto\RequestParameter;

use Callisto\RequestParameter;


class Location extends RequestParameter
{
	/**
	 * @var string
	 */
	protected $key = 'locations';


	/**
	 * @return string
	 */
	public function getValue() : string
	{
		$values = array_map(function($value) {
			if (is_array($value)) {
				return implode(',', $value);
			}

			return $value;
		}, $this->value);

		return implode(',', $values);
	}
}
