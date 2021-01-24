# mediawiki-sitematrix-api

[![GitHub issue custom search in repo](https://img.shields.io/github/issues-search/addwiki/addwiki?label=issues&query=is%3Aissue%20is%3Aopen%20%5Bmediawiki-sitematrix-api%5D)](https://github.com/addwiki/addwiki/issues?q=is%3Aissue+is%3Aopen+%5Bmediawiki-sitematrix-api%5D+)

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
