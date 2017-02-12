<?php

namespace Callisto;
use Callisto\RequestParameter\Language;
use Callisto\RequestParameter\Track;
use Callisto\Stream\Filter;
use PHPUnit\Framework\TestCase;

/**
 * Overwrite PHP's built in function to read the test stream.
 *
 * @param $url
 * @param $port
 * @return resource
 */
function fsockopen($url, $port)
{
	return fopen(__DIR__ . '/stream_logs/' . StreamTest::$callistoTestStreamLog, 'r');
}

/**
 * Git won't allow to store \r\n line endings, but the stream has them,
 * so we need to switch the \n\n endings in the test file.
 *
 * @param $handle
 * @param $length
 * @return string
 */
function fread($handle, $length)
{
	$str = \fread($handle, $length);

	if ("\n\n" == substr($str, $length - 2, 2)) {
		$str = substr($str, 0, $length - 2) . "\r\n";
	}

	return $str;
}

/**
 * We will not have to write into the test stream.
 *
 * @param $res
 * @param $str
 * @param $len
 * @return bool
 */
function fwrite($res, $str, $len)
{
	return true;
}

/**
 * Created by PhpStorm.
 * User: tamaskovacs
 * Date: 2017. 01. 31.
 * Time: 0:21
 */
class StreamTest extends TestCase
{
	public static $callistoTestStreamLog;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $logger;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	protected $oauth;

	/**
	 * @var Stream
	 */
	protected $stream;


	public function setUp()
	{
		$this->logger = $this->createMock(Logger::class);
		$this->oauth = $this->getMockBuilder(Oauth::class)
			->setConstructorArgs([1, 2, 3, 4])
			->getMock();

		$this->stream = new Filter($this->oauth);
		$this->stream->setLogger($this->logger);
	}

	public function testConnect()
	{
		self::$callistoTestStreamLog = 'test.log';

		$this->oauth->expects($this->once())
			->method('getOauthRequest')
			->with(['stall_warnings' => 'true'], 'POST', Stream::BASE_URL, '/1.1/statuses/filter.json')
			->willReturn('');

		$this->logger->expects($this->exactly(2))
			->method('info')
			->withConsecutive(
				['Opening new connection.'],
				['Connection successful.']
			);

		$this->stream->connect();
	}

	public function testNoDuplicatedConnection()
	{
		self::$callistoTestStreamLog = 'test.log';

		$this->oauth->expects($this->once())
			->method('getOauthRequest')
			->with(['stall_warnings' => 'true'], 'POST', Stream::BASE_URL, '/1.1/statuses/filter.json')
			->willReturn('');

		$this->logger->expects($this->exactly(3))
			->method('info')
			->withConsecutive(
				['Opening new connection.'],
				['Connection successful.'],
				['Connection already opened.']
			);

		$this->stream->connect();
		$this->stream->connect();
	}

	public function testConnectionError()
	{
		$this->expectException('\Callisto\Exception\ConnectionException');
		self::$callistoTestStreamLog = 'error401.log';

		$this->logger->expects($this->once())
			->method('info')
			->with('Opening new connection.');

		$this->logger->expects($this->once())
			->method('critical')
			->with('Connection error');

		$this->stream->connect();
	}

	public function testConnectionClosed()
	{
		$this->expectException('\Callisto\Exception\ConnectionClosedException');
		self::$callistoTestStreamLog = 'connection_closed.log';

		$this->stream->connect();

		$statuses = [];
		foreach ($this->stream->readStream() as $jsonStatus) {
			$statuses[] = json_decode($jsonStatus);
		}
	}

	public function testReadASingleTweet()
	{
		self::$callistoTestStreamLog = 'test.log';

		$this->stream->connect();

		$statuses = [];
		foreach ($this->stream->readStream() as $jsonStatus) {
			$statuses[] = json_decode($jsonStatus);
		}

		$this->assertEquals(1, count($statuses));
		$this->assertEquals(826518635371913216, $statuses[0]->id);
	}

	public function testReadMultipleStatuses()
	{
		self::$callistoTestStreamLog = 'multiple_tweets.log';

		$this->stream->connect();

		$statuses = [];
		foreach ($this->stream->readStream() as $jsonStatus) {
			$statuses[] = json_decode($jsonStatus);
		}

		$this->assertEquals(3, count($statuses));
		$this->assertEquals(826518635371913216, $statuses[0]->id);
		$this->assertEquals(826569797970305024, $statuses[1]->id);
		$this->assertEquals(826569798150660096, $statuses[2]->id);
	}

	public function testRequestParametersAreUsed()
	{
		self::$callistoTestStreamLog = 'test.log';

		$this->oauth->expects($this->once())
			->method('getOauthRequest')
			->with([
				'stall_warnings' => 'true',
				'track' => 'twitter,facebook',
				'language' => 'en,de',
			], 'POST', Stream::BASE_URL, '/1.1/statuses/filter.json')
			->willReturn('');

		$this->logger->expects($this->exactly(2))
			->method('info')
			->withConsecutive(
				['Opening new connection.'],
				['Connection successful.']
			);

		$track = $this->getMockBuilder(Track::class)
			->setConstructorArgs(['twitter', 'facebook'])
			->getMock();

		$language = $this->getMockBuilder(Language::class)
			->setConstructorArgs(['en', 'de'])
			->getMock();

		$track->expects($this->once())
			->method('getKey')
			->willReturn('track');
		$track->expects($this->once())
			->method('getValue')
			->willReturn('twitter,facebook');

		$language->expects($this->once())
			->method('getKey')
			->willReturn('language');
		$language->expects($this->once())
			->method('getValue')
			->willReturn('en,de');

		$this->stream->setRequestParameters(
			[$track, $language]
		);
		$this->stream->connect();
	}
}
