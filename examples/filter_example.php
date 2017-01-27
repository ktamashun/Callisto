<?php

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

include __DIR__ . '/../vendor/autoload.php';

$consumerKey = 'xWp3LhsRcO7wYyFeveniIACLp';
$consumerSecret = 'Y0uv4hXkba45ajOM2WFzxJmBV7hiyZc1kBSC8KOq0UpOlnebHr';
$accessTokenSecret = 'lJvuqqtYJLyvfr73GQmiyfqEAstxxLbnOXkK1ZSEXWPZc';
$accessToken = '27311622-mjbZEeMlGD9lv8OedufCCAMg9qUUVI1dgHNl5734B';

$logger = new Logger('Callisto');
$logger->pushHandler(new StreamHandler('php://stdout'));

$oauth = new \Callisto\Oauth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
$stream = new \Callisto\Stream\Filter($oauth);
$stream->setLogger($logger);
$stream->setRequestParameters(
	[
		new Callisto\RequestParameter\Track(['twitter']),
		new Callisto\RequestParameter\Language(['en', 'de']),
		//new Callisto\Filter\Follow(['123456789', '987654321']),
		//new Callisto\Filter\Location(10.1101, 12.001, 30.223, 35.443),
	]
);

foreach ($stream->readStream() as $jsonStatus) {
	$status = json_decode($jsonStatus);

	if (!isset($status->id)) {
		echo $jsonStatus . PHP_EOL;
		$this->logger->info('Message', [$status]);
	}

	$this->logger->info('New status: ' . $status->id, [$status->text]);
}
