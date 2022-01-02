# wikibase-query

[![GitHub issue custom search in repo](https://img.shields.io/github/issues-search/addwiki/addwiki?label=issues&query=is%3Aissue%20is%3Aopen%20%5Bwikibase-query%5D)](https://github.com/addwiki/addwiki/issues?q=is%3Aissue+is%3Aopen+%5Bwikibase-query%5D+)
[![Latest Stable Version](https://poser.pugx.org/addwiki/wikibase-query/version.png)](https://packagist.org/packages/addwiki/wikibase-query)
[![Download count](https://poser.pugx.org/addwiki/wikibase-query/d/total.png)](https://packagist.org/packages/addwiki/wikibase-query)

Issue tracker: https://github.com/addwiki/addwiki/issues

## Installation

Use composer to install the library and all its dependencies:

    composer require "addwiki/wikibase-query:~3.0"

## Examples

Use the `SimpleQueryService` with wikidata.

```php
use Addwiki\Wikibase\Query\WikibaseQueryFactory;
use Addwiki\Wikibase\Query\PrefixSets;

$factory = new WikibaseQueryFactory(
    "https://query.wikidata.org/sparql",
    PrefixSets::WIKIDATA
);

$r = $factory->newSimpleQueryService()->query(["P31:Q1"]);
```