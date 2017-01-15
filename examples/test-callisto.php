<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

include __DIR__ . '/../vendor/autoload.php';

$consumerKey = 'xWp3LhsRcO7wYyFeveniIACLp';
$consumerSecret = 'Y0uv4hXkba45ajOM2WFzxJmBV7hiyZc1kBSC8KOq0UpOlnebHr';
$accessTokenSecret = 'lJvuqqtYJLyvfr73GQmiyfqEAstxxLbnOXkK1ZSEXWPZc';
$accessToken = '27311622-mjbZEeMlGD9lv8OedufCCAMg9qUUVI1dgHNl5734B';

class myFilter extends Callisto\Stream\Filter
{
	public function enqueueStatus($jsonStatus)
	{
		$status = json_decode($jsonStatus);

		if (!isset($status->id)) {
			echo $jsonStatus . PHP_EOL;
			$this->logger->info('Message', [$status]);
		}
		//$this->logger->info('New status: ' . $status->id, [$status->text]);
	}
}

$logger = new Logger('Callisto');
$logger->pushHandler(new StreamHandler('php://stdout'));

$stream = new myFilter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$stream->setLogger($logger);
$stream->setFilters(
	[
		new Callisto\Filter\Track(['twitter']),
		//new Callisto\Filter\Language(['en', 'de']),
		//new Callisto\Filter\Follow(['123456789', '987654321']),
		//new Callisto\Filter\Location(10.1101, 12.001, 30.223, 35.443),
	]
);
$stream->readStream();
