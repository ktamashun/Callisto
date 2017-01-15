<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 14.
 * Time: 16:11
 */

namespace Callisto;


/**
 * Class BaseFilter
 * @package Callisto
 */
abstract class BaseFilter
{
	/**
	 * @var string
	 */
	protected $key;

	/**
	 * @var array
	 */
	protected $value;


	/**
	 * BaseFilter constructor.
	 * @param array $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function getKey() : string
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getValue() : string
	{
		return implode(',', $this->value);
	}
}