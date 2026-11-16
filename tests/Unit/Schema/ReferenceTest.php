<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use Spiral\JsonSchemaGenerator\Schema\Reference;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Testo\Assert;
use Testo\Data\DataProvider;
use Testo\Test;

#[Test]
final class ReferenceTest
{
    public static function referencesDataProvider(): \Traversable
    {
        yield [Movie::class, '#/definitions/Movie'];
        yield [\stdClass::class, '#/definitions/stdClass'];
    }

    #[DataProvider('referencesDataProvider')]
    public function testReference(string $class, string $expected): void
    {
        Assert::equals((new Reference($class))->jsonSerialize(), $expected);
    }
}
