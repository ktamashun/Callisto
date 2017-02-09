# Callisto

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Build Status](https://scrutinizer-ci.com/g/ktamashun/callisto/badges/build.png?b=master)](https://scrutinizer-ci.com/g/ktamashun/callisto/build-status/master)
[![Total Downloads][ico-downloads]][link-downloads]

**PHP library for Twitter Streaming API.**

Twitteer Streaming API documentation:
https://dev.twitter.com/streaming/overview

## Install

The esiest way to install Callisto is using composer

`$ composer require ktamashun/callisto`

## Usage

### Create a Twitter app

First go to https://apps.twitter.com/ and create a new Twitter app. To authenticate to the Twitter Streaming API you are going to need a `CONSUMER_KEY`, `CONSUMER_SECRET`, `ACCESS_TOKEN`, `ACCESS_TOKEN_SECRET`. These can be found on the Keys and access tokens tab within your application.

### Running the examples

The examples can be found in the `examples` directory.

The directory contains a sample config file: `env.sample.php`. Use this to create a local one: `env.local.php` and fill in the `CONSUMER_KEY`, `CONSUMER_SECRET`, `ACCESS_TOKEN`, `ACCESS_TOKEN_SECRET` constants with your applications.

The esiest way to run the examples is to use a [Docker](https://www.docker.com/) container:

`$ docker run -it --rm -v $(pwd):/www/ -w /www/examples php:7.1-alpine php filter_example.php`

### Using the filter stream

There are five type of filters in the `\Callisto\RequestParameters` namespace.

* `FilterLevel`: This can be used to filter out tweets that would not be appropriate during a presentation.
* `Follow`: You can use this parameter follow the activity of certain users.
* `Language`: Filter tweets that were written in one or more given languages.
* `Location`: Filter tweets that were written in a certain geographic area. Please read carefully Twitter's documentation about location filtering.
* `Track`: You can track specific words.

You can read about fiiter parameters in detail in the [Twitter API documentation](https://dev.twitter.com/streaming/overview/request-parameters).

Example usage:

```php
$oauth = new \Callisto\Oauth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
$stream = new \Callisto\Stream\Filter($oauth);
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
		new Callisto\RequestParameter\Follow(['123456789', '987654321']),
		// Set filter level for the stream
		new Callisto\RequestParameter\FilterLevel(Callisto\RequestParameter\FilterLevel::LOW)
	]
);

foreach ($stream->readStream() as $jsonStatus) {
	echo $jsonStatus;
}
```

## Versioning

This library follows [SemVer v2.0.0.](http://semver.org/)

## Testing

The library is tested using PHPUnit. You can run the test like:

`$ ./vendor/phpunit/phpunit ./tests`

## Credits

[Tamás Kovács](https://github.com/ktamashun)

## Licence

The MIT License (MIT). Please see the License File for more information.

[ico-version]: https://img.shields.io/packagist/v/ktamashun/callisto.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/ktamashun/callisto.svg
[ico-code-quality]: https://img.shields.io/scrutinizer/g/ktamashun/callisto.svg
[ico-downloads]: https://img.shields.io/packagist/dt/ktamashun/callisto.svg
[ico-build-status]: https://scrutinizer-ci.com/g/ktamashun/callisto/badges/build.png?b=master

[link-packagist]: https://packagist.org/packages/ktamashun/callisto
[link-scrutinizer]: https://scrutinizer-ci.com/g/ktamashun/callisto/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/ktamashun/callisto
[link-downloads]: https://packagist.org/packages/ktamashun/callisto
[link-buid-status]: https://scrutinizer-ci.com/g/ktamashun/callisto/build-status/master
[link-author]: https://github.com/ktamashun
