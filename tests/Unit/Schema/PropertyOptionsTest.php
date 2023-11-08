<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Schema\PropertyOptions;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class PropertyOptionsTest extends TestCase
{
    public function testPropertyOptions(): void
    {
        $options = new PropertyOptions([
            Movie::class,
            Type::Integer,
        ]);

        $this->assertEquals([
            [
                '$ref' => '#/definitions/Movie',
            ],
            [
                'type' => 'integer'
            ],
        ], $options->jsonSerialize());
    }
}
