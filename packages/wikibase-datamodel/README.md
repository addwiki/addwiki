# wikibase-datamodel

[![GitHub issue custom search in repo](https://img.shields.io/github/issues-search/addwiki/addwiki?label=issues&query=is%3Aissue%20is%3Aopen%20%5Bwikibase-datamodel%5D)](https://github.com/addwiki/addwiki/issues?q=is%3Aissue+is%3Aopen+%5Bwikibase-datamodel%5D+)
[![Latest Stable Version](https://poser.pugx.org/addwiki/wikibase-datamodel/version.png)](https://packagist.org/packages/addwiki/wikibase-datamodel)
[![Download count](https://poser.pugx.org/addwiki/wikibase-datamodel/d/total.png)](https://packagist.org/packages/addwiki/wikibase-datamodel)

Issue tracker: https://github.com/addwiki/addwiki/issues

This library is generally only for use by other addwiki libraries.

There are probably not many usecases where you would want to install this package alone.

## Installation

Use composer to install the library and all its dependencies:

    composer require "addwiki/wikibase-datamodel:~3.0"

#### Load

```php
require_once( __DIR__ . '/vendor/autoload.php' );
```

## External Libraries

Some code, such as `MediaInfo` realted code is pulled in from MediaWiki extensions and can be found in the `/lib` directory.
This is because this code is not availible as a library, but there is little point in rewriting it...

This code can be updated using the `sync-copied-files` composer command.

- MediaInfo is pinned at `d86d961a0eb0c28e9b5d8ce600c64a9dae973533` which is just before the 2021 DataModel changes, which this library is not yet adapted for.