<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Php74\Rector\LNumber\AddLiteralSeparatorToNumberRector;
use Rector\Php80\Rector\FuncCall\ClassOnObjectRector;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use RectorLaravel\Set\LaravelLevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/config',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    // Define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::EARLY_RETURN,
        SetList::TYPE_DECLARATION,
        SetList::PRIVATIZATION,
        LaravelLevelSetList::UP_TO_LARAVEL_110,
    ]);

    // Skip some rules that might not be appropriate for Laravel
    $rectorConfig->skip([
        AddLiteralSeparatorToNumberRector::class,
        ClassOnObjectRector::class,
        ReadOnlyPropertyRector::class,
    ]);

    // Configure specific rules
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // Import names configuration
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();

    // Skip paths that shouldn't be refactored
    $rectorConfig->skip([
        __DIR__ . '/vendor',
        __DIR__ . '/bootstrap',
        __DIR__ . '/storage',
        __DIR__ . '/public/build',
        __DIR__ . '/node_modules',
    ]);

    // Parallel processing
    $rectorConfig->parallel();
};
