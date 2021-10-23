# mediawiki-api-base

addwiki/mediawiki-api-base is a PHP HTTP client wrapped around guzzle that makes it easy to interact with a MediaWiki installation.

1. Uses PSR-3 interfaces for logging
2. Handles Mediawiki login, sessions, cookies and tokens
3. Handles response errors by throwing catchable UsageExceptions
4. Retries failed requests where possible
5. Allows Async requests

## Requirements

PHP 7.2+
Guzzle HTTP library ~6.0

## Contributing

This package is developed as part of a Mono Repo.

See https://github.com/addwiki/addwiki
