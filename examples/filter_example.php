<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'env.local.php';

$logger = new \Callisto\Logger();

$oauth = new \Callisto\Oauth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
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
		$logger->info('Message', [$status]);
	}

	//$logger->info('New status: ' . $status->id, [$status->text]);
}
