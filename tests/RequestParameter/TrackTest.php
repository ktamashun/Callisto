<?php
/**
 * Created by PhpStorm.
 * User: ktamashun <kovacs.tamas.hun@gmail.com>
 * Date: 2017. 02. 12.
 * Time: 18:37
 */

use Callisto\RequestParameter\Track;

class TrackTest extends \PHPUnit\Framework\TestCase
{
	public function testGetKeyGetValue()
	{
		$track = new Track(['twitter', 'facebook']);
		$this->assertEquals('track', $track->getKey());
		$this->assertEquals('twitter,facebook', $track->getValue());
	}
}
