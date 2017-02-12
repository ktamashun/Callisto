<?php
/**
 * Created by PhpStorm.
 * User: ktamashun <kovacs.tamas.hun@gmail.com>
 * Date: 2017. 02. 12.
 * Time: 18:37
 */

use Callisto\RequestParameter\Location;

class LocationTest extends \PHPUnit\Framework\TestCase
{
	public function testGetKeyGetValue()
	{
		$track = new Location([
			[-74, 40, -73, 41],
			[-122.75, 36.8, -121.75, 37.8],
			100
		]);
		$this->assertEquals('locations', $track->getKey());
		$this->assertEquals('-74,40,-73,41,-122.75,36.8,-121.75,37.8,100', $track->getValue());
	}
}
