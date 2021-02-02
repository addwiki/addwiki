<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // where are the packages located?
    $packageDirs = [
        __DIR__ . '/packages'
    ];
    if( !getenv( 'MONOREPO_NO_DEV' ) ) {
        $packageDirs[] = __DIR__ . '/packages-dev';
    }
    $parameters->set(Option::PACKAGE_DIRECTORIES, $packageDirs);

    // for "merge" command
    $parameters->set(Option::DATA_TO_APPEND, [
        ComposerJsonSection::REQUIRE_DEV => [
            'phpunit/phpunit' => '~9',
        ],
    ]);
};
