# Category Traversal

The CategoryTraverser class is used to start at one Category page in a wiki’s category hierarchy and descend through that category’s children, grandchildren, and so on. The basic output of this is a Pages object containing all the pages in the category tree. It is also possible to register callbacks that will be called for every subcategory or other page (i.e. anything not a category).

## Basic usage

To get all pages in a category or any of its subcategories.

```php
// Construct the API.
$api = new \Addwiki\Mediawiki\Api\Client\Action\MediawikiApi( 'http://localhost/w/api.php' );
$services = new \Addwiki\Mediawiki\Api\MediawikiFactory( $api );
$categoryTraverser = $services->newCategoryTraverser();

// Get the root category.
$rootCatIdent = new PageIdentifier( new Title( 'Category:Categories' ) );
$rootCat = $services->newPageGetter()->getFromPageIdentifier( $pageIdentifier );

// Get all pages.
$allPages = $categoryTraverser->descend( $rootCat );
```
