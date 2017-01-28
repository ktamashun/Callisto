<?php
/**
 * @author: Tamás Kovács <kovacs.tamas.hun@gmail.com>
 */

namespace Callisto\RequestParameter;

use Callisto\RequestParameter;


class FilterLevel extends RequestParameter
{
	const NONE = 'none';
	const LOW = 'low';
	const MEDIUM = 'medium';


	/**
	 * @var string
	 */
	protected $key = 'filter_level';
}
