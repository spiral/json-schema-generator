<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class PhpDocConstraints
{
    public function __construct(
        /** @var negative-int */
        public int $negative,
        /** @var non-positive-int */
        public int $nonPositive,
        /** @var non-negative-int */
        public int $nonNegative,
        /** @var int<min, 5> */
        public int $upTo,
        /** @var int<1, max> */
        public int $from,
        /** @var class-string */
        public string $className,
        /** @var array{name: string, age?: int, score: float, active: bool} */
        public array $shape,
        /** @var array{0: string, 1: int} */
        public array $tuple,
    ) {}
}
