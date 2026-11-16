<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use Spiral\JsonSchemaGenerator\Schema\PropertyOption;
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

    public function testArrayAccessAndCount(): void
    {
        $options = new PropertyOptions([Type::String]);

        $options[1] = Movie::class;
        $options[2] = new PropertyOption(Type::Integer);

        Assert::same(\count($options), 3);
        Assert::true(isset($options[1]));
        Assert::same($options[1]->value, Movie::class);
        Assert::same($options[2]->value, Type::Integer);
        Assert::same(\count($options->getOptions()), 3);

        unset($options[0]);

        Assert::false(isset($options[0]));
        Assert::same($options->jsonSerialize(), [
            ['$ref' => '#/definitions/Movie'],
            ['type' => 'integer'],
        ]);
    }
}
