# Getting Namespaces

The Name Space Getter allows you to search for namespaces and their aliases and to list all namespaces of a wiki.

To use it, first get a new NamespaceGetter object from the factory:

```php
$api = new \Addwiki\Mediawiki\Api\Client\MediawikiApi( 'http://localhost/w/api.php' );
$services = new \Addwiki\Mediawiki\Api\Client\MediawikiFactory( $api );
$namespaceGetter = $services->newNamespaceGetter();
```

## Looking for a namespace

If you’ve got a page name like `File:awesome_cats.jpg` and want to know its namespace ID and possible localized names and aliases, use the following code:

```php
$fileNamespace = $namespaceGetter->getNamespaceByName( 'File' );
printf( "Name in local language: %s\n", $fileNamespace->getLocalName() );
printf( "Possible aliases: %s\n", implode( ', ', $fileNamespace->getAliases() ) );
// ... etc
```

`getNamespaceByName` accepts the canonical name, the local name and aliases. If you want to match only the canonical name, use `getNamespaceByCanonicalName` instead.

## Getting a namespaced page

If you have a page title that is not in the default namespace, you can’t pass the page name string `PageGetter` but must construct a `Title` object instead:

```php
$pageName = 'User:MalReynolds';
$nameParts = explode( ':', $pageName, 2 );
$namespace = $namespaceGetter->getNamespaceByName( $nameParts[0] );
$title = new \Addwiki\Mediawiki\DataModel\Title( $nameParts[1], $namespace->getId() );
$page = $services->newPageGetter()->getFromTitle( $title );
```

## Listing all namespaces

```php
foreach( $namespaceGetter->getNamespaces() as $namespace ) {
   echo $namespace->getLocalName() .  "\n";
}
```
