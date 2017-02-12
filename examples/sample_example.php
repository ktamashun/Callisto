<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'env.local.php';

$logger = new \Callisto\Logger(fopen('php://stdout', 'w'));

$oauth = new \Callisto\Oauth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
$stream = new \Callisto\Stream\Sample($oauth);
$stream->setLogger($logger);

foreach ($stream->readStream() as $jsonStatus) {
	$status = json_decode($jsonStatus);
	$logger->info('New status: ' . $status->id, [$status->text]);
}

$stream->close();
