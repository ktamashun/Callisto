<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'env.local.php';

$logger = new \Callisto\Logger(fopen('php://stdout', 'w'));

$oauth = new \Callisto\Oauth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
$stream = new \Callisto\Stream\Filter($oauth);
$stream->setLogger($logger);
$stream->setRequestParameters(
	[
		// Track custom phrases
		new Callisto\RequestParameter\Track(['twitter']),

		// Filter Tweets by language
		new Callisto\RequestParameter\Language(['en', 'de']),

		// Filter tweets from New York or San Francisco
		new Callisto\RequestParameter\Location(
			[
				[-74, 40, -73, 41],
				[-122.75, 36.8, -121.75, 37.8],
			]
		),

		// Follow specific users
		//new Callisto\RequestParameter\Follow(['123456789', '987654321']),

		// Set filter level for the stream
		//new Callisto\RequestParameter\FilterLevel(Callisto\RequestParameter\FilterLevel::LOW)
	]
);

foreach ($stream->readStream() as $jsonStatus) {
	$status = json_decode($jsonStatus);
	$logger->info('New status: ' . $status->id, [$status->text]);
}
