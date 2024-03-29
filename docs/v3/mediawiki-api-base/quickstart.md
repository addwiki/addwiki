# Quickstart

This page provides a quick introduction to this library and introductory examples. If you have not already installed the library head over to the Installation page.

## Getting an API object

You can get an api object by simply passing the api endpoint:

```php
use \Addwiki\Mediawiki\Api\Client\Action\ActionApi;

$api = ActionApi::newFromApiEndpoint( 'https://en.wikipedia.org/w/api.php' );
```

You can even just pass a page:

```php
use \Addwiki\Mediawiki\Api\Client\Action\ActionApi;

$api = ActionApi::newFromPage( 'https://en.wikipedia.org/wiki/Berlin' );
```

## Logging in and out

To log in:

```php
use Addwiki\Mediawiki\Api\Client\Auth\UserAndPassword;
use Addwiki\Mediawiki\Api\Client\MediaWiki;

$userAndPassword = new UserAndPassword( 'username', 'password' );
$api = MediaWiki::newFromEndpoint( 'https://en.wikipedia.org/w/api.php', $userAndPassword );
// Subsequent API calls will be authenticated.
```

## Making request objects

The library provides two different way of constructing requests.

```php
use Addwiki\Mediawiki\Api\Client\Action\Request\ActionRequest;

$purgeRequest = new ActionRequest::simplePost( 'purge', [ 'titles' => 'Berlin' ] );
// or
$purgeRequest = ActionRequest::factory()->setMethod( 'POST' )->setAction( 'purge' )->setParam( 'titles', 'Berlin' );
```

## Sending requests

```php
$api->action()->request( $purgeRequest );

$queryResponse = $api->action()->request( ActionRequest::factory()->setMethod( 'GET' )->setAction( 'query' )->setParam( 'meta', 'siteinfo' ) );

try {
    $api->action()->request( ActionRequest::simpleGet( 'FooBarBaz' ) );
} catch ( UsageException $e ) {
    echo "The api returned an error!";
}
```

## Making async requests

```php
// Initiate each request but do not block
$requestPromises = array(
    'Page1' => $api->postRequestAsync( FluentRequest::factory()->setAction( 'purge' )->setParam( 'titles', 'Page1' ) ),
    'Page2' => $api->postRequestAsync( FluentRequest::factory()->setAction( 'purge' )->setParam( 'titles', 'Page2' ) ),
    'Page3' => $api->postRequestAsync( FluentRequest::factory()->setAction( 'purge' )->setParam( 'titles', 'Page3' ) ),
);

// Wait on all of the requests to complete.
$results = GuzzleHttp\Promise\unwrap( $requestPromises );

// You can access each result using the key provided to the unwrap function.
print_r( $results['Page1'], $results['Page2'], $results['Page3'] )
```
