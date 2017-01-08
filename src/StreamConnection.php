<?php
/**
 * Created by PhpStorm.
 * User: kovacs.tamas.hun@gmail.com
 * Date: 2017. 01. 06.
 * Time: 22:16
 */

namespace Callisto;


class StreamConnection
{
	const HOST = 'stream.twitter.com';
	const PROTOCOL = 'https';


	/**
	 * Connection resource.
	 *
	 * @var resource
	 */
	protected $connection;

	/**
	 * OAuth2 access token.
	 *
	 * @var string
	 */
	protected $accessToken;

	/**
	 * Twitter app consumer key.
	 *
	 * @var string
	 */
	private $consumerKey;


	public function __construct($consumerKey, $accessToken)
	{
		$this->consumerKey = $consumerKey;
		$this->accessToken = $accessToken;
	}

	/**
	 * Opens a connection to the Twitter Streaming API.
	 */
	public function connect()
	{
		$params = [

		];
		$params = array_merge($params, $this->getOauthParams());
	}

	private function getOauthParams()
	{
		return [
			'oauth_consumer_key' => $this->consumerKey,
			'oauth_nonce' => md5(rand() . mktime()),
			'oauth_signature' => '???',
			'oauth_signature_method' => "HMAC-SHA1",
			'oauth_timestamp' => mktime(),
			'oauth_token' => $this->accessToken,
			'oauth_version' => '1.0',
		];
	}

}