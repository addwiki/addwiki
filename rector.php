<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;

use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector;

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

	$parameters->set(
		Option::SETS,
		[
			SetList::CODING_STYLE,
			SetList::CODE_QUALITY,
			SetList::CODE_QUALITY_STRICT,
			SetList::DEAD_CODE,
			SetList::PHPUNIT_90,
			SetList::PHPUNIT_91,
			SetList::PHPUNIT_CODE_QUALITY,
			SetList::PHPUNIT_EXCEPTION,
			SetList::PHPUNIT_MOCK,
			SetList::PSR_4,
			SetList::PHP_70,
			SetList::PHP_71,
			SetList::PHP_72,
			SetList::PHP_73,
			SetList::PHP_74,
			// Disabled until https://github.com/rectorphp/rector/issues/5612 is fixed
			//SetList::TYPE_DECLARATION,
		]
	);

	$parameters->set(
		Option::SKIP,
		[
			JsonThrowOnErrorRector::class,
			CamelCaseFunctionNamingToUnderscoreRector::class,
		]
	);

};
