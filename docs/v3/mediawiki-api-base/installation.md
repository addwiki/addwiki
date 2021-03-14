# Overview

## Installation

The recommended way to install this library is with Composer. Composer is a dependency management tool for PHP that allows you to declare the dependencies your project needs and installs them into your project.

```sh
composer require addwiki/mediawiki-api-base:~2.0
```

Alternatively, you can specify addwiki/mediawiki-api-base as a dependency in your project’s existing composer.json file:

```json
{
   "require": {
      "addwiki/mediawiki-api-base": "~2.0"
   }
}
```

After installing, you need to require Composer’s autoloader:

```php
require 'vendor/autoload.php';
```

You can find out more on how to install Composer, configure autoloading, and other best-practices for defining dependencies at getcomposer.org.

## Bleeding edge

During your development, you can keep up with the latest changes on the master branch by setting the version requirement for addwiki/mediawiki-api-base to `~2.0@dev`.

```json
{
   "require": {
      "addwiki/mediawiki-api-base": "~2.0@dev"
   }
}
```