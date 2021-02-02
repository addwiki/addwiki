# addwiki - monorepo

Addwiki is a collection of PHP libraries, packages and applications created for interatcting wit MediaWiki, Wikibase, Wikimedia and more.

To dive in take a look at the [docs site](https://addwiki.github.io/).

If you want to submit code patches to any of the repositories, then this is the place to look!

## Packages

All packages exist in the `/packages` directory.
Every package also exists in it's own read only git repository, can be used separately and is installable via composer.

**Most popular:**

- [mediawiki-api-base](https://github.com/addwiki/mediawiki-api-base)
- [mediawiki-datamodel](https://github.com/addwiki/mediawiki-datamodel)
- [mediawiki-api](https://github.com/addwiki/mediawiki-api)
- [wikibase-api](https://github.com/addwiki/wikibase-api)

**WIP CLI:**

- [addwiki-cli](https://github.com/addwiki/addwiki-cli)
- [mediawiki-commands](https://github.com/addwiki/mediawiki-commands)
- [wikibase-commands](https://github.com/addwiki/wikibase-commands)
- [wikimedia-commands](https://github.com/addwiki/wikimedia-commands)

**Other WIP:**

- [wikimedia](https://github.com/addwiki/wikimedia)
- [mediawiki-sitematrix-api](https://github.com/addwiki/mediawiki-sitematrix-api)
- [mediawiki-flow-api](https://github.com/addwiki/mediawiki-flow-api)
- [mediawiki-db](https://github.com/addwiki/mediawiki-db)

## Using the monorepo

This mono repo uses https://github.com/symplify/monorepo-builder

This provides convenience scripts for a few things...

Merge all composer.json files together with:

```sh
vendor/bin/monorepo-builder merge
```

Bump the cross package dependency with:

```sh
vendor/bin/monorepo-builder bump-interdependency "<version here>"
```

Validate your synchronization:

```sh
vendor/bin/monorepo-builder validate
```

Keep your package aliases up to date (not yet working)

```sh
vendor/bin/monorepo-builder package-alias
```