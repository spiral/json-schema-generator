<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use Spiral\JsonSchemaGenerator\Schema\PropertyOptions;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Testo\Assert;
use Testo\Test;

#[Test]
final class PropertyOptionsTest
{
    public function testPropertyOptions(): void
    {
        $options = new PropertyOptions([
            Movie::class,
            Type::Integer,
        ]);

        Assert::equals($options->jsonSerialize(), [
            [
                '$ref' => '#/definitions/Movie',
            ],
            [
                'type' => 'integer',
            ],
        ]);
    }
}
