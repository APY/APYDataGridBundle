<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\PHPUnit\Set\PHPUnitSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        //__DIR__ . '/src',
        __DIR__ . '/Tests'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
       $rectorConfig->sets([
           LevelSetList::UP_TO_PHP_74,
           LevelSetList::UP_TO_PHP_80,
           PHPUnitSetList::PHPUNIT_91
       ]);
};
