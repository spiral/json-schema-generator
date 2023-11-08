<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Schema\Reference;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class ReferenceTest extends TestCase
{
    #[DataProvider('referencesDataProvider')]
    public function testReference(string $class, string $expected): void
    {
        $this->assertEquals($expected, (new Reference($class))->jsonSerialize());
    }

    public static function referencesDataProvider(): \Traversable
    {
        yield [Movie::class, '#/definitions/Movie'];
        yield [\stdClass::class, '#/definitions/stdClass'];
    }
}
