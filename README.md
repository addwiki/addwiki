## addwiki mono repo

This mono repo uses https://github.com/symplify/monorepo-builder

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