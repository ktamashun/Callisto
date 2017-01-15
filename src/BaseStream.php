<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 14.
 * Time: 16:32
 */

namespace Callisto;


use Psr\Log\LoggerInterface;

abstract class BaseStream
{
	/**
	 * Streaming API base URL.
	 */
	const BASE_URL = 'https://stream.twitter.com';


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
	 * Connection resource.
	 *
	 * @var resource
	 */
	private $connection;

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
	 * Streaming endpoint.
	 *
	 * @var string
	 */
	protected $endpoint = '';

	/**
	 * Http method to use when connecting to the streaming API.
	 *
	 * @var string
	 */
	protected $httpMethod = 'GET';

	/**
	 * @var LoggerInterface
	 */
	protected $logger;


	abstract public function enqueueStatus($jsonStatus);

	/**
	 * Stream constructor.
	 *
	 * @param $consumerKey
	 * @param $consumerSecret
	 * @param $accessToken
	 * @param $accessTokenSecret
	 */
	public function __construct($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret)
	{
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->accessToken = $accessToken;
		$this->accessTokenSecret = $accessTokenSecret;
	}

	/**
	 * Opens a connection to the Twitter Streaming API.
	 */
	public function connect() : void
	{
		if (is_resource($this->connection)) {
			$this->logger->info('Connection already opened.');
			return;
		}

		$this->logger->info('Opening new connection.');

		$params = $this->getParams();
		$params = array_merge($params, $this->getOauthParams());
		$params['oauth_signature'] = $this->getOauthSignature($params);
		$request = $this->getOauthRequest($params);

		$this->connection = fsockopen('ssl://stream.twitter.com', 443);
		stream_set_blocking($this->connection, true);
		fwrite($this->connection, $request, strlen($request));

		$response = [];
		while (!feof($this->connection)) {
			$line = trim((string)fgets($this->connection, 1024));
			if (empty($line)) {
				break;
			}

			$response[] = $line;
		}

		preg_match('/^HTTP\/1\.1 ([0-9]{3}).*$/', $response[0], $matches);
		if (200 !== (int)$matches[1]) {
			$this->logger->critical('Connection error', [$response[0]]);
			throw new \Exception('Connection error: ' . $response[0]);
		}

		$this->logger->info('Connection successful.', $response);
	}

	/**
	 * Returns the request parameters.
	 *
	 * @return array
	 */
	protected function getParams() : array
	{
		return [];
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
			'oauth_nonce' => md5(mktime().rand()),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_timestamp' => mktime(),
			'oauth_token' => $this->accessToken,
			'oauth_version' => '1.0',
		];
	}

	/**
	 * Generates an OAuth signature.
	 *
	 * @param array $params
	 * @return string
	 */
	private function getOauthSignature($params) : string
	{
		$urlParams = [];
		foreach ($params as $key => $value) {
			$params[$key] = rawurlencode($value);
			$urlParams[$key] = $key . '=' . $params[$key];
		}

		ksort($urlParams);
		$parameterString = implode('&', $urlParams);
		$signatureBaseString = strtoupper($this->httpMethod) .
			'&' . rawurlencode(self::BASE_URL . $this->endpoint) .
			'&' . rawurlencode($parameterString);

		$signingKey = rawurlencode($this->consumerSecret) . '&' . rawurlencode($this->accessTokenSecret);
		$oauthSignature = base64_encode(hash_hmac('sha1', $signatureBaseString, $signingKey, true));

		return $oauthSignature;
	}

	/**
	 * Generates the HTTP request string.
	 *
	 * @param array $params
	 * @return string
	 */
	private function getOauthRequest($params) : string
	{
		$requestParams = [];

		foreach ($this->getParams() as $key => $param) {
			$requestParams[] = $key . '=' . rawurlencode($param);
		}

		$content = implode('&', $requestParams);

		return $this->httpMethod . " " . $this->endpoint . " HTTP/1.1\r\n"
			."Accept: */*\r\n"
			."Connection: close\r\n"
			."User-Agent: Callisto API\r\n"
			."Content-Type: application/x-www-form-urlencoded\r\n"
			."Authorization: OAuth realm=\"\",oauth_consumer_key=\"".$params['oauth_consumer_key']."\","
			."oauth_nonce=\"".$params['oauth_nonce']."\","
			."oauth_signature_method=\"".$params['oauth_signature_method']."\","
			."oauth_timestamp=\"".$params['oauth_timestamp']."\","
			."oauth_version=\"".$params['oauth_version']."\","
			."oauth_token=\"".$params['oauth_token']."\","
			."oauth_signature=\"".rawurlencode($params['oauth_signature'])."\"\r\n"
			."Content-Length: " . strlen($content) . "\r\n"
			."Host: stream.twitter.com:443\r\n\r\n"
			.$content
		;
	}

	/**
	 * Sets the logger instance.
	 *
	 * @param LoggerInterface $logger
	 * @return BaseStream $this Fluent interface.
	 */
	public function setLogger(LoggerInterface $logger) : BaseStream
	{
		$this->logger = $logger;
		return $this;
	}

	/**
	 * Reads the next chunk of $chunkSize from the stream.
	 *
	 * @param int $chunkSize
	 * @return string
	 */
	protected function readChunk($chunkSize) : string
	{
		return \fread($this->connection, $chunkSize);
	}

	/**
	 * Reads the size of the next chunk from the stream.
	 *
	 * @return int
	 * @throws \Exception
	 */
	protected function readNextChunkSize() : int
	{
		while (!feof($this->connection)) {
			$line = trim((string)fgets($this->connection));

			if (!empty($line)) {
				$chunkSize = hexdec($line);
				return (int)$chunkSize;
			}
		}

		$this->logger->error('Connection closed.');
		throw new \Exception('Connection closed.');
	}

	/**
	 * Connects to the Twitter API and starts reading the stream.
	 *
	 * When it recieves a new status it will be passed on to the @link self::enqueueStatus() method.
	 *
	 * @return void
	 */
	public function readStream() : void
	{
		$this->connect();

		$status = '';
		while (!feof($this->connection)) {
			$chunkSize = $this->readNextChunkSize();

			if (2 == $chunkSize) {
				continue;
			}

			$chunk = $this->readChunk($chunkSize);
			$status .= $chunk;

			if ("\r\n" == substr($chunk, $chunkSize - 2, 2)) {
				$this->enqueueStatus($status);
				$status = '';
			}
		}
	}
}
