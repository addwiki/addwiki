<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJsonSection;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::DEFAULT_BRANCH_NAME, 'main');

    // where are the packages located?
    $packageDirs = [
        __DIR__ . '/packages'
    ];
    if( !getenv( 'MONOREPO_NO_DEV' ) ) {
        $packageDirs[] = __DIR__ . '/packages-dev';
    }
    $parameters->set(Option::PACKAGE_DIRECTORIES, $packageDirs);
};
