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
	]
);

$stream->connect();
while (!$stream->eof()) {
	echo $stream->read(2048);
}
