<?php

namespace Callisto;
use PHPUnit\Framework\TestCase;

/**
 * We need the time function to always return the same value.
 *
 * @return int
 */
function time()
{
	return 1;
}

/**
 * We need the rand function to always return the same value.
 *
 * @return int
 */
function rand()
{
	return 2;
}

/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 31.
 * Time: 0:09
 */
class OauthTest extends TestCase
{
	public function testGetOauthRequest()
	{
		$expectedAuthRequest = <<<REQ
POST /1.1/statuses/filter.jsopn HTTP/1.1
Accept: */*
Connection: close
User-Agent: Callisto API
Content-Type: application/x-www-form-urlencoded
Authorization: OAuth realm="",oauth_consumer_key="1",oauth_nonce="c20ad4d76fe97759aa27a0c99bff6710",oauth_signature_method="HMAC-SHA1",oauth_timestamp="1",oauth_version="1.0",oauth_token="3",oauth_signature="gZiLU3ve4W%2Bt45O4qVj2l%2B%2FT5VI%3D"
Content-Length: 13
Host: stream.twitter.com:443

track=twitter
REQ;

		$oauth = new \Callisto\Oauth(1, 2, 3, 4);
		$authRequest = $oauth->getOauthRequest(
			['track' => 'twitter'],
			'POST',
			'https://stream.twitter.com',
			'/1.1/statuses/filter.jsopn'
		);
		$this->assertEquals($expectedAuthRequest, str_replace("\r\n", "\n", $authRequest));
	}
}
