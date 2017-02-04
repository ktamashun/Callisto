<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 22.
 * Time: 17:14
 */

namespace Callisto;


class Oauth
{
	/**
	 * OAuth2 access token.
	 *
	 * @var string
	 */
	private $accessToken;

	/**
	 * OAuth2 access token secret.
	 *
	 * @var string
	 */
	private $accessTokenSecret;

	/**
	 * Twitter app consumer key.
	 *
	 * @var string
	 */
	private $consumerKey;

	/**
	 * Twitter app consumer secret.
	 *
	 * @var string
	 */
	private $consumerSecret;


	/**
	 * Oauth constructor.
	 *
	 * @param string $consumerKey
	 * @param string $consumerSecret
	 * @param string $accessToken
	 * @param string $accessTokenSecret
	 */
	public function __construct(string $consumerKey, string $consumerSecret, string $accessToken, string $accessTokenSecret)
	{
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->accessToken = $accessToken;
		$this->accessTokenSecret = $accessTokenSecret;
	}

	/**
	 * Returns the parameters for an OAuth request.
	 *
	 * @return array
	 */
	private function getOauthParams() : array
	{
		return [
			'oauth_consumer_key' => $this->consumerKey,
			'oauth_nonce' => md5(mktime() . rand()),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => mktime(),
			'oauth_token' => $this->accessToken,
			'oauth_version' => '1.0',
		];
	}

	/**
	 * Generates the HTTP request string.
	 *
	 * @param array $params
	 * @param $httpMethod
	 * @param $baseUrl
	 * @param $endPoint
	 * @return string
	 */
	public function getOauthRequest(array $params, $httpMethod, $baseUrl, $endPoint) : string
	{
		$signatureParams = array_merge($params, $this->getOauthParams());
		$signatureParams['oauth_signature'] = $this->getOauthSignature($signatureParams, $httpMethod, $baseUrl . $endPoint);

		$contentParams = [];
		foreach ($params as $key => $param) {
			$contentParams[] = $key . '=' . rawurlencode($param);
		}

		$content = implode('&', $contentParams);

		return $httpMethod . " " . $endPoint . " HTTP/1.1\r\n"
		."Accept: */*\r\n"
		."Connection: close\r\n"
		."User-Agent: Callisto API\r\n"
		."Content-Type: application/x-www-form-urlencoded\r\n"
		."Authorization: OAuth realm=\"\",oauth_consumer_key=\"" . $signatureParams['oauth_consumer_key'] . "\","
		."oauth_nonce=\"" . $signatureParams['oauth_nonce'] . "\","
		."oauth_signature_method=\"" . $signatureParams['oauth_signature_method'] . "\","
		."oauth_timestamp=\"" . $signatureParams['oauth_timestamp'] . "\","
		."oauth_version=\"" . $signatureParams['oauth_version'] . "\","
		."oauth_token=\"" . $signatureParams['oauth_token'] . "\","
		."oauth_signature=\"" . rawurlencode($signatureParams['oauth_signature']) . "\"\r\n"
		."Content-Length: " . strlen($content) . "\r\n"
		."Host: stream.twitter.com:443\r\n\r\n"
		.$content;
	}

	/**
	 * Generates an OAuth signature.
	 *
	 * @param array $params
	 * @param $httpMethod
	 * @param $url
	 * @return string
	 */
	private function getOauthSignature(array $params, $httpMethod, $url) : string
	{
		$urlParams = [];
		foreach ($params as $key => $value) {
			$params[$key] = rawurlencode($value);
			$urlParams[$key] = $key . '=' . $params[$key];
		}

		ksort($urlParams);
		$parameterString = implode('&', $urlParams);
		$signatureBaseString = strtoupper($httpMethod) .
			'&' . rawurlencode($url) .
			'&' . rawurlencode($parameterString);

		$signingKey = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->accessTokenSecret);
		$oauthSignature = base64_encode(hash_hmac('sha1', $signatureBaseString, $signingKey, true));

		return $oauthSignature;
	}
}