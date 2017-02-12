<?php
/**
 * Created by PhpStorm.
 * User: ktamashun <kovacs.tamas.hun@gmail.com>
 * Date: 2017. 02. 12.
 * Time: 18:37
 */

use Callisto\RequestParameter\FilterLevel;

class FilterLevelTest extends \PHPUnit\Framework\TestCase
{
	public function testGetKeyGetValue()
	{
		$track = new FilterLevel(FilterLevel::LOW);
		$this->assertEquals('filter_level', $track->getKey());
		$this->assertEquals(FilterLevel::LOW, $track->getValue());
	}
}
