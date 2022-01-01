<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;

use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return static function (ContainerConfigurator $containerConfigurator): void {
	$parameters = $containerConfigurator->parameters();
	$services = $containerConfigurator->services();

	// paths to refactor; solid alternative to CLI arguments
	$parameters->set(
		Option::PATHS,
		[
		__DIR__ . '/packages',
		]
	);

	// Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
	$parameters->set(
		Option::AUTOLOAD_PATHS,
		[
		__DIR__ . '/packages',
		]
	);

	$parameters->set(
		Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);
	$parameters->set(
		Option::AUTO_IMPORT_NAMES, true);

	$containerConfigurator->import(SetList::CODING_STYLE);
	$containerConfigurator->import(SetList::CODE_QUALITY);
	$containerConfigurator->import(SetList::CODE_QUALITY_STRICT);
	$containerConfigurator->import(SetList::DEAD_CODE);
	$containerConfigurator->import(SetList::PSR_4);
	$containerConfigurator->import(SetList::PHP_70);
	$containerConfigurator->import(SetList::PHP_71);
	$containerConfigurator->import(SetList::PHP_72);
	$containerConfigurator->import(SetList::PHP_73);
	$containerConfigurator->import(SetList::PHP_74);
	// Disabled until https://github.com/rectorphp/rector/issues/5612 is fixed
	//$containerConfigurator->import(SetList::TYPE_DECLARATION);

	$containerConfigurator->import(PHPUnitSetList::PHPUNIT_90);
	$containerConfigurator->import(PHPUnitSetList::PHPUNIT_91);
	$containerConfigurator->import(PHPUnitSetList::PHPUNIT_CODE_QUALITY);
	$containerConfigurator->import(PHPUnitSetList::PHPUNIT_EXCEPTION);
	$containerConfigurator->import(PHPUnitSetList::PHPUNIT_MOCK);

	$parameters->set(
		Option::SKIP,
		[
			JsonThrowOnErrorRector::class,
			CamelCaseFunctionNamingToUnderscoreRector::class,
		]
	);

};
