<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 27.
 * Time: 20:24
 */

namespace Callisto;


use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
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


	/**
	 * Stream constructor.
	 *
	 * @param $consumerKey
	 * @param $consumerSecret
	 * @param $accessToken
	 * @param $accessTokenSecret
	 */
	public function __construct(string $consumerKey, string $consumerSecret, string $accessToken, string $accessTokenSecret)
	{
		$this->consumerKey = $consumerKey;
		$this->consumerSecret = $consumerSecret;
		$this->accessToken = $accessToken;
		$this->accessTokenSecret = $accessTokenSecret;

		$this->logger = new NullLogger();
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
	private function getOauthSignature(array $params) : string
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
	private function getOauthRequest(array $params) : string
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
	 * Handles the message from twitter.
	 *
	 * @param string $messageJson
	 */
	private function handleMessage(string $messageJson) : void
	{
		$message = json_decode($messageJson);
		$this->logger->info('Message received', [$message]);
	}

	/**
	 * Determines if the received json is message from twitter.
	 *
	 * @param string $status
	 * @return bool
	 */
	private function isMessage(string $status) : bool
	{
		$testStr = substr($status, 0, 14);
		if ('{"created_at":' == $testStr) {
			return false;
		}

		return true;
	}

	/**
	 * Override the default NullLogger
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
	protected function readChunk(int $chunkSize) : string
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
				if ($this->isMessage($status)) {
					$this->handleMessage($status);
				} else {
					$this->enqueueStatus($status);
				}

				$status = '';
			}
		}
	}











	/**
	 * @throws \Exception
	 */
	public function __toString()
	{
		throw new \RuntimeException('Cannot read entire stream.');
	}

	/**
	 * Closes the stream and any underlying resources.
	 *
	 * @return void
	 */
	public function close() : void
	{
		fclose($this->connection);
	}

	/**
	 * Separates any underlying resources from the stream.
	 *
	 * After the stream has been detached, the stream is in an unusable state.
	 *
	 * @return resource|null Underlying PHP stream, if any
	 */
	public function detach() : null
	{
		$this->close();
		return null;
	}

	/**
	 * We do not know the size of the stream.
	 *
	 * @return null
	 */
	public function getSize() : null
	{
		return null;
	}

	/**
	 * We do not have the position of the cursor.
	 *
	 * @return null
	 * @throws \RuntimeException on error.
	 */
	public function tell() : null
	{
		return null;
	}

	/**
	 * Returns true if the stream is at the end of the stream.
	 *
	 * @return bool
	 */
	public function eof() : bool
	{
		return feof($this->connection);
	}

	/**
	 * The stream is not seekable.
	 *
	 * @return bool
	 */
	public function isSeekable() : bool
	{
		return false;
	}

	/**
	 * The stream is not seekable.
	 *
	 * @throws \RuntimeException on failure.
	 */
	public function seek($offset, $whence = SEEK_SET) : void
	{
		throw new \RuntimeException('The stream is not seekable.');
	}

	/**
	 * The stream cannot be rewind.
	 *
	 * @throws \RuntimeException on failure.
	 */
	public function rewind() : void
	{
		throw new \RuntimeException('The stream cannot be rewind.');
	}

	/**
	 * Returns whether or not the stream is writable.
	 *
	 * @return bool
	 */
	public function isWritable() : bool
	{
		return true;
	}

	/**
	 * Write data to the stream.
	 *
	 * @param string $string The string that is to be written.
	 * @return int Returns the number of bytes written to the stream.
	 * @throws \RuntimeException on failure.
	 */
	public function write($string) : int
	{
		$length = strlen($string);
		fwrite($this->connection, $string, $length);

		return $length;
	}

	/**
	 * Returns whether or not the stream is readable.
	 *
	 * @return bool
	 */
	public function isReadable() : bool
	{
		return true;
	}

	/**
	 * Read data from the stream.
	 *
	 * @param int $length Read up to $length bytes from the object and return
	 *     them. Fewer than $length bytes may be returned if underlying stream
	 *     call returns fewer bytes.
	 * @return string Returns the data read from the stream, or an empty string
	 *     if no bytes are available.
	 * @throws \RuntimeException if an error occurs.
	 */
	public function read($length)
	{
		return fread($this->connection, $length);
	}

	/**
	 * Reads the nex line fron the stream.
	 *
	 * @param int $length Read up to $length bytes from the object and return
	 *     them. Fewer than $length bytes may be returned if underlying stream
	 *     call returns fewer bytes.
	 * @return string Returns the data read from the stream, or an empty string
	 *     if no bytes are available.
	 * @throws \RuntimeException if an error occurs.
	 */
	public function readLine($length = 1024)
	{
		return fgets($this->connection, $length);
	}

	/**
	 * Returns the remaining contents in a string
	 *
	 * @return string
	 * @throws \RuntimeException if unable to read or an error occurs while
	 *     reading.
	 */
	public function getContents()
	{
		throw new \RuntimeException('We cannot return the rest of the stream.');
	}

	/**
	 * Get stream metadata as an associative array or retrieve a specific key.
	 *
	 * The keys returned are identical to the keys returned from PHP's
	 * stream_get_meta_data() function.
	 *
	 * @link http://php.net/manual/en/function.stream-get-meta-data.php
	 * @param string $key Specific metadata to retrieve.
	 * @return array|mixed|null Returns an associative array if no key is
	 *     provided. Returns a specific key value if a key is provided and the
	 *     value is found, or null if the key is not found.
	 */
	public function getMetadata($key = null)
	{
		return stream_get_meta_data($this->connection);
	}
}