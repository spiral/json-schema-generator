<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\PhpDocConstraints;
use Testo\Assert;
use Testo\Skip;
use Testo\Test;

#[Test]
final class PhpDocConstraintsTest
{
    public function testNegativeInt(): void
    {
        Assert::same(self::property('negative'), ['type' => 'integer', 'maximum' => -1]);
    }

    #[Skip('non-positive-int also matches positive-int, see https://github.com/spiral/json-schema-generator/issues/38')]
    public function testNonPositiveInt(): void
    {
        Assert::same(self::property('nonPositive'), ['type' => 'integer', 'maximum' => 0]);
    }

    #[Skip('non-negative-int also matches negative-int, see https://github.com/spiral/json-schema-generator/issues/38')]
    public function testNonNegativeInt(): void
    {
        Assert::same(self::property('nonNegative'), ['type' => 'integer', 'minimum' => 0]);
    }

    public function testIntRangeWithOpenLowerBound(): void
    {
        Assert::same(self::property('upTo'), ['type' => 'integer', 'maximum' => 5]);
    }

    public function testIntRangeWithOpenUpperBound(): void
    {
        Assert::same(self::property('from'), ['type' => 'integer', 'minimum' => 1]);
    }

    public function testClassString(): void
    {
        Assert::same(self::property('className'), [
            'type' => 'string',
            'pattern' => '^[a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\\\]*$',
            'description' => 'Must be a valid PHP class name',
        ]);
    }

    public function testArrayShapeWithScalarTypesAndOptionalKey(): void
    {
        Assert::same(self::property('shape'), [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'age' => ['type' => 'integer'],
                'score' => ['type' => 'number'],
                'active' => ['type' => 'boolean'],
            ],
            'additionalProperties' => false,
            'required' => ['name', 'score', 'active'],
        ]);
    }

    private static function property(string $name): array
    {
        return (new Generator())->generate(PhpDocConstraints::class)->jsonSerialize()['properties'][$name];
    }
}
