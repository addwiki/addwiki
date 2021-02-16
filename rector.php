<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/packages' ]);

    // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/packages',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
        SetList::CODE_QUALITY_STRICT,
        SetList::PHPUNIT_90,
        SetList::PHPUNIT_91,
        SetList::PHPUNIT_CODE_QUALITY,
        SetList::PHPUNIT_EXCEPTION,
        SetList::PHPUNIT_MOCK,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        // TODO use below set when adding type declarations
        //SetList::TYPE_DECLARATION,
        ]);
};
