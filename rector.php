<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\Core\ValueObject\PhpVersion;

use Rector\CodingStyle\Rector\Assign\ManualJsonStringToJsonEncodeArrayRector;
use Rector\CodingStyle\Rector\Assign\PHPStormVarAnnotationRector;
use Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Rector\CodingStyle\Rector\Class_\AddArrayDefaultToArrayPropertyRector;
use Rector\CodingStyle\Rector\ClassConst\SplitGroupedConstantsAndPropertiesRector;
use Rector\CodingStyle\Rector\ClassConst\VarConstantCommentRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Rector\CodingStyle\Rector\ClassMethod\RemoveDoubleUnderscoreInMethodNameRector;
use Rector\CodingStyle\Rector\ClassMethod\UnSpreadOperatorRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Rector\CodingStyle\Rector\FuncCall\CallUserFuncCallToVariadicRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\FuncCall\FunctionCallToConstantRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\CodingStyle\Rector\Function_\CamelCaseFunctionNamingToUnderscoreRector;
use Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Rector\CodingStyle\Rector\Include_\FollowRequireByDirRector;
use Rector\CodingStyle\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector;
use Rector\CodingStyle\Rector\Plus\UseIncrementAssignRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\CodingStyle\Rector\Property\AddFalseDefaultToBoolPropertyRector;
use Rector\CodingStyle\Rector\String_\SplitStringClassConstantToClassConstFetchRector;
use Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Rector\CodingStyle\Rector\Switch_\BinarySwitchToIfElseRector;
use Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector;
use Rector\CodingStyle\Rector\Use_\RemoveUnusedAliasRector;
use Rector\CodingStyle\Rector\Use_\SplitGroupedUseImportsRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();
    $services = $containerConfigurator->services();

    // paths to refactor; solid alternative to CLI arguments
    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages',
        __DIR__ . '/packages-dev',
        __DIR__ . '/bin',
        ]);

    // Rector relies on autoload setup of your project; Composer autoload is included by default; to add more:
    $parameters->set(Option::AUTOLOAD_PATHS, [
        __DIR__ . '/packages',
        __DIR__ . '/packages-dev',
        __DIR__ . '/bin',
    ]);

    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_72);
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    $parameters->set(Option::SETS, [
        SetList::CODE_QUALITY,
        SetList::CODE_QUALITY_STRICT,
        SetList::DEAD_CODE,
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

    // Manual tweak of SetList::CODING_STYLE https://github.com/rectorphp/rector/blob/master/config/set/coding-style.php
    $services->set(PHPStormVarAnnotationRector::class);
    $services->set(NullableCompareToNullRector::class);
    $services->set(BinarySwitchToIfElseRector::class);
    $services->set(ConsistentImplodeRector::class);
    $services->set(TernaryConditionVariableAssignmentRector::class);
    $services->set(RemoveUnusedAliasRector::class);
    $services->set(SymplifyQuoteEscapeRector::class);
    $services->set(SplitGroupedConstantsAndPropertiesRector::class);
    $services->set(SplitStringClassConstantToClassConstFetchRector::class);
    $services->set(StringClassNameToClassConstantRector::class);
    $services->set(ConsistentPregDelimiterRector::class);
    $services->set(FollowRequireByDirRector::class);
    $services->set(CatchExceptionNameMatchingTypeRector::class);
    $services->set(UseIncrementAssignRector::class);
    $services->set(SplitDoubleAssignRector::class);
    $services->set(VarConstantCommentRector::class);
    $services->set(EncapsedStringsToSprintfRector::class);
    $services->set(WrapEncapsedVariableInCurlyBracesRector::class);
    $services->set(NewlineBeforeNewAssignSetRector::class);
    $services->set(ManualJsonStringToJsonEncodeArrayRector::class);
    $services->set(AddArrayDefaultToArrayPropertyRector::class);
    $services->set(AddFalseDefaultToBoolPropertyRector::class);
    $services->set(MakeInheritedMethodVisibilitySameAsParentRector::class);
    $services->set(CallUserFuncCallToVariadicRector::class);
    $services->set(VersionCompareFuncCallToConstantRector::class);
    $services->set(UseMessageVariableForSprintfInSymfonyStyleRector::class);

    $services->set(FunctionCallToConstantRector::class)
        ->call('configure', [[
            FunctionCallToConstantRector::FUNCTIONS_TO_CONSTANTS => [
                'php_sapi_name' => 'PHP_SAPI',
                'pi' => 'M_PI',
            ],
        ]]);

    // Removed as this project doesn't use camel case
    //$services->set(CamelCaseFunctionNamingToUnderscoreRector::class);
    $services->set(SplitGroupedUseImportsRector::class);
    $services->set(RemoveDoubleUnderscoreInMethodNameRector::class);
    $services->set(PostIncDecToPreIncDecRector::class);
    $services->set(UnSpreadOperatorRector::class);
};
