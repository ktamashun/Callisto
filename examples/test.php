<?php

include __DIR__ . '/../vendor/autoload.php';

use GuzzleHttp\Psr7;

$res = fsockopen('ssl://stream.twitter.com', 443);
$str = "POST /1.1/statuses/filter.json?track=twitter HTTP/1.1\n\r"
    ."Accept: */*\n\r"
    ."Connection: close\n\r"
    ."User-Agent: OAuth gem v0.4.4\n\r"
    ."Content-Type: application/x-www-form-urlencoded\n\r"
    ."Authorization:\n\r"
    ."  OAuth oauth_consumer_key=\"xWp3LhsRcO7wYyFeveniIACLp\",\n\r"
    ."              oauth_nonce=\"".md5('hello')."\",\n\r"
    ."              oauth_signature=\"\",\n\r"
    ."              oauth_signature_method=\"HMAC-SHA1\",\n\r"
    ."              oauth_timestamp=\"".mktime()."\",\n\r"
    ."              oauth_token=\"27311622-mjbZEeMlGD9lv8OedufCCAMg9qUUVI1dgHNl5734B\",\n\r"
    ."              oauth_version=\"1.0\"\n\r"
    ."Content-Length: 76\n\r"
    ."Host: api.twitter.com";

fwrite($res, $str);
echo fread($res, 1000);
var_dump($res);

/*
$resource = fopen('https://stream.twitter.com/1.1/statuses/filter.json?track=twitter', 'r');
$stream = Psr7\stream_for($resource);

echo $stream->read(1000).PHP_EOL;
echo $stream->getMetadata('uri');
// /path/to/file
var_export($stream->isReadable());
// true
var_export($stream->isWritable());
// false
var_export($stream->isSeekable());
*/