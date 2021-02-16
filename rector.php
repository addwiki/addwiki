<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;
use Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayKeysAndInArrayToArrayKeyExistsRector;
use Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Rector\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector;
use Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\If_\CombineIfRector;
use Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;

use Rector\Order\Rector\Class_\OrderClassConstantsByIntegerValueRector;
use Rector\Order\Rector\Class_\OrderConstantsByVisibilityRector;
use Rector\Order\Rector\Class_\OrderFirstLevelClassStatementsRector;
use Rector\Order\Rector\Class_\OrderPropertiesByVisibilityRector;

use Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Rector\Php72\Rector\While_\WhileEachToForeachRector;

use Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;

use Rector\PHPUnit\Rector\MethodCall\AssertCompareToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Rector\PHPUnit\Rector\MethodCall\AssertEqualsToSameRector;
use Rector\PHPUnit\Rector\MethodCall\AssertInstanceOfComparisonRector;

use Rector\Privatization\Rector\Class_\FinalizeClassesWithoutChildrenRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [__DIR__ . '/packages' ]);

    // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/packages',
    ]);

    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
        SetList::PHPUNIT_90,
        SetList::PHPUNIT_91,
        SetList::PHPUNIT_CODE_QUALITY,
        SetList::PHPUNIT_EXCEPTION,
        SetList::PHPUNIT_MOCK,
        SetList::PHP_70,
        SetList::PHP_71,
        SetList::PHP_72,
        ]);

    // // is your PHP version different from the one your refactor to? [default: your PHP version], uses PHP_VERSION_ID format
    // $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);

    // // auto import fully qualified class names? [default: false]
    // $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // $services = $containerConfigurator->services();
    // $services->set(TypedPropertyRector::class);
    // $services->set(AbsolutizeRequireAndIncludePathRector::class);
    // $services->set(ArrayKeyExistsTernaryThenValueToCoalescingRector::class);
    // $services->set(ArrayKeysAndInArrayToArrayKeyExistsRector::class);
    // $services->set(ArrayMergeOfNonArraysToSimpleArrayRector::class);
    // $services->set(ArrayThisCallToThisMethodCallRector::class);
    // $services->set(BooleanNotIdenticalToNotIdenticalRector::class);
    // $services->set(ChangeArrayPushToArrayAssignRector::class);
    // $services->set(CombineIfRector::class);
    // $services->set(CombinedAssignRector::class);
    // $services->set(CommonNotEqualRector::class);
    // $services->set(CompleteDynamicPropertiesRector::class);

    // $services->set(OrderClassConstantsByIntegerValueRector::class);
    // $services->set(OrderConstantsByVisibilityRector::class);
    // $services->set(OrderFirstLevelClassStatementsRector::class);
    // $services->set(OrderPropertiesByVisibilityRector::class);

    // $services->set(StringifyDefineRector::class);
    // $services->set(WhileEachToForeachRector::class);

    // $services->set(RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class);
    // $services->set(RenameParamToMatchTypeRector::class);
    // $services->set(RenamePropertyToMatchTypeRector::class);
    // $services->set(RenameVariableToMatchMethodCallReturnTypeRector::class);
    // $services->set(RenameVariableToMatchNewTypeRector::class);

    // $services->set(AssertCompareToSpecificMethodRector::class);
    // $services->set(AssertComparisonToSpecificMethodRector::class);
    // $services->set(AssertEqualsToSameRector::class);
    // $services->set(AssertInstanceOfComparisonRector::class);

    //$services->set(FinalizeClassesWithoutChildrenRector::class);
};
