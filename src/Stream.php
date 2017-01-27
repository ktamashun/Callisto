<?php
/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 14.
 * Time: 16:32
 */

namespace Callisto;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Stream extends Psr7Stream
{
	/**
	 * Streaming API base URL.
	 */
	const BASE_URL = 'https://stream.twitter.com';


	/**
	 * @var Oauth
	 */
	protected $oauth;

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


	public function enqueueStatus(string $jsonStatus)
	{}

	/**
	 * Stream constructor.
	 *
	 * @param Oauth $oauth
	 */
	public function __construct(Oauth $oauth)
	{
		$this->oauth = $oauth;
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

		$request = $this->oauth->getOauthRequest(
			$this->getParams(),
			$this->httpMethod,
			self::BASE_URL,
			$this->endpoint
		);

		$this->connection = fsockopen('ssl://stream.twitter.com', 443);
		stream_set_blocking($this->connection, true);
		$this->write($request);

		$response = [];
		while (!$this->eof()) {
			$line = trim((string)$this->readLine());
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
	 * @return Stream $this Fluent interface.
	 */
	public function setLogger(LoggerInterface $logger) : Stream
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
		return $this->read($chunkSize);
	}

	/**
	 * Reads the size of the next chunk from the stream.
	 *
	 * @return int
	 * @throws \Exception
	 */
	protected function readNextChunkSize() : int
	{
		while (!$this->eof()) {
			$line = trim($this->readLine());

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
	 * @return \Generator
	 */
	public function readStream() : \Generator
	{
		$this->connect();

		$status = '';
		while (!$this->eof()) {
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
					yield $status;
					//$this->enqueueStatus($status);
				}

				$status = '';
			}
		}
	}
 }
