<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/includes',
    ])
    ->withSkip([
        __DIR__ . '/includes/views',
        __DIR__ . '/vendor',
    ])
    ->withPhpSets(
        php82: true,
    )
    ->withSets([
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::STRICT_BOOLEANS,
        LevelSetList::UP_TO_PHP_82,
    ]);

