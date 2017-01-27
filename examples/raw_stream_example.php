<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'env.local.php';

$logger = new \Callisto\Logger('Callisto');

$oauth = new \Callisto\Oauth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
$stream = new \Callisto\Stream\Filter($oauth);
$stream->setLogger($logger);
$stream->setRequestParameters(
	[
		new Callisto\RequestParameter\Track(['twitter']),
		new Callisto\RequestParameter\Language(['en', 'de']),
	]
);

$stream->connect();
while (!$stream->eof()) {
	echo $stream->read(2048);
}
