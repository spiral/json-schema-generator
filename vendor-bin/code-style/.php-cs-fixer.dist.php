<?php

declare(strict_types=1);

// Lives next to the isolated php-cs-fixer install in vendor-bin/code-style/; source paths reach the project root via ../../.
require_once __DIR__ . '/vendor/autoload.php';

return \Spiral\CodeStyle\Builder::create()
    ->include(__DIR__ . '/../../src')
    ->include(__DIR__ . '/../../tests')
    ->include(__FILE__)
    ->cache(__DIR__ . '/../../runtime/php-cs-fixer.cache')
    ->allowRisky(false)
    ->build();
