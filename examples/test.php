<?php

include __DIR__ . '/../vendor/autoload.php';

$consumerKey = 'xWp3LhsRcO7wYyFeveniIACLp';
$consumerSecret = 'Y0uv4hXkba45ajOM2WFzxJmBV7hiyZc1kBSC8KOq0UpOlnebHr';
$accessTokenSecret = 'lJvuqqtYJLyvfr73GQmiyfqEAstxxLbnOXkK1ZSEXWPZc';
$accessToken = '27311622-mjbZEeMlGD9lv8OedufCCAMg9qUUVI1dgHNl5734B';


$http_method = 'POST';
$base_url = 'https://stream.twitter.com/1.1/statuses/filter.json';
$track = 'twitter';

$params = [
	'track' => $track,
	'oauth_consumer_key' => $consumerKey,
	'oauth_nonce' => 'cc8b0762b0c7194d7938be5d41ec2012', //md5(rand()),
	'oauth_signature_method' => 'HMAC-SHA1',
	'oauth_timestamp' => '1484414234', //mktime(),
	'oauth_token' => $accessToken,
	'oauth_version' => '1.0',
];

/*
$consumerKey = 'xvz1evFS4wEEPTGEFPHBog';
$consumerSecret = 'kAcSOqF21Fu85e7zjz7ZN2U4ZRhfV3WpwPAoE3Z7kBw';
$accessTokenSecret = 'LswwdoUaIvS8ltyTt5jkRh4J50vUPVVHtR2YPi5kE';
$accessToken = '370773112-GmHxMAgYyLbNEtIKZeRNFsMKPR9EyMZeS9weJAEb';

$http_method = 'POST';
$base_url = 'https://api.twitter.com/1/statuses/update.json';
$params = [
	'status' => 'Hello Ladies + Gentlemen, a signed OAuth request!',
	'include_entities' => 'true',
	'oauth_consumer_key' => $consumerKey,
	'oauth_nonce' => 'kYjzVBB8Y0ZFabxSWbWovY3uYSQ2pTgmZeNu2VS4cg',
	'oauth_signature_method' => 'HMAC-SHA1',
	'oauth_timestamp' => '1318622958',
	'oauth_token' => $accessToken,
	'oauth_version' => '1.0',
];
*/

$urlParams = [];
foreach ($params as $key => $value) {
	$params[$key] = rawurlencode($value);
	$urlParams[$key] = $key . '=' . $params[$key];
}

ksort($urlParams);
$parameterString = implode('&', $urlParams);
$signatureBaseString = strtoupper($http_method) .
	'&' . rawurlencode($base_url) .
	'&' . rawurlencode($parameterString);

$signingKey = rawurlencode($consumerSecret) . '&' . rawurlencode($accessTokenSecret);
$oauthSignature = base64_encode(hash_hmac('sha1', $signatureBaseString, $signingKey, true));


$str = "POST /1.1/statuses/filter.json HTTP/1.1\r\n"
    ."Accept: */*\r\n"
	."Connection: close\r\n"
	."User-Agent: Callisto API\r\n"
	."Content-Type: application/x-www-form-urlencoded\r\n"
    ."Authorization: OAuth realm=\"\",oauth_consumer_key=\"".$params['oauth_consumer_key']."\","
    ."oauth_nonce=\"".$params['oauth_nonce']."\","
    ."oauth_signature_method=\"".$params['oauth_signature_method']."\","
    ."oauth_timestamp=\"".$params['oauth_timestamp']."\","
	."oauth_version=\"".$params['oauth_version']."\","
    ."oauth_token=\"".$params['oauth_token']."\","
	."oauth_signature=\"".rawurlencode($oauthSignature)."\"\r\n"
	."Content-Length: 13\r\n"
	."Host: stream.twitter.com:443\r\n\r\n"
	."track=".$track
;

echo $str . PHP_EOL; return;

$res = fsockopen('ssl://stream.twitter.com', 443);
stream_set_blocking($res, 1);
fwrite($res, $str, strlen($str));


while (true && !feof($res)) {
	$line = fgets($res, 1024);
	if (empty($line)) {
		continue;
	}

	echo $line;
}
return;




while ($line = trim(fgets($res, 1024))) {
	echo $line.'====================='.PHP_EOL;
	echo json_decode($line);
}
while (true && !feof($res)) {
	$line = trim(fgets($res, 1024));
	if (empty($line)) {
		continue;
	}

	echo '______________________________________________'.PHP_EOL;
	$status = '';
	while (true) {
		$chunkLength = hexdec($line);
		$chunk = fread($res, $chunkLength);
		$status .= $chunk;

		if ($chunk !== trim($chunk)) {
			fgets($res, 1024); // empty line
			break;
		}

		$line = fgets($res, 1024); // empty line
		$line = trim(fgets($res, 1024)); // next chunk size

		/*if (empty($line)) {
			break;
		}*/
	}

	if (!empty(trim($status))) {
		echo $status.PHP_EOL;
		echo '=============================================='.PHP_EOL;
	}
}
return;

