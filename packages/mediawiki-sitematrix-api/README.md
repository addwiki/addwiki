mediawiki-sitematrix-api
==================

Issue tracker: https://phabricator.wikimedia.org/project/profile/1490/

## Installation

Use composer to install the library and all its dependencies:

    composer require "addwiki/mediawiki-sitematrix-api:~0.1.0"

## Example Usage

```php
// Load all the stuff
require_once( __DIR__ . '/vendor/autoload.php' );

// Log in to a wiki
$api = new \Mediawiki\Api\MediawikiApi( 'http://localhost/w/api.php' );
$services = new \Mediawiki\SiteMatrix\Api\MediawikiSitematrixFactory( $api );

// Get the sitelist
$siteList = $services->newSiteListGetter()->getSiteList();
```
