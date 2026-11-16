<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Schema\Format;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Schema\PropertyType;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Actor;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Test;

#[Test]
final class PropertyTest
{
    public function testPropertyOnlyRequiredParams(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)]);

        Assert::equals($property->jsonSerialize(), ['type' => 'string']);
    }

    public function testPropertyWithTitle(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], title: 'Some movie');

        Assert::equals($property->jsonSerialize(), [
            'type' => 'string',
            'title' => 'Some movie',
        ]);
    }

    public function testPropertyWithDescription(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], description: 'Some description');

        Assert::equals($property->jsonSerialize(), [
            'type' => 'string',
            'description' => 'Some description',
        ]);
    }

    public function testPropertyWithDefault(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], default: 'value');

        Assert::equals($property->jsonSerialize(), [
            'type' => 'string',
            'default' => 'value',
        ]);
    }

    public function testPropertyWithUnionType(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Movie::class), new PropertyType(type: Actor::class)],
        );

        Assert::equals($property->jsonSerialize(), [
            'oneOf' => [
                [
                    '$ref' => '#/definitions/Movie',
                ],
                [
                    '$ref' => '#/definitions/Actor',
                ],
            ],
        ]);
    }

    public function testPropertyWithClassType(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Movie::class)],
        );

        Assert::equals($property->jsonSerialize(), [
            '$ref' =>  '#/definitions/Movie',
        ]);
    }

    public function testPropertyWithArrayTypeSingleClassElem(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Type::Array, collectionTypes: [new PropertyType(type: Movie::class)])],
            title: 'Some movie',
        );

        Assert::equals($property->jsonSerialize(), [
            'type' => 'array',
            'title' => 'Some movie',
            'items' => [
                '$ref' => '#/definitions/Movie',
            ],
        ]);
    }

    public function testPropertyWithArrayTypeSingleScalarElem(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Type::Array, collectionTypes: [new PropertyType(type: Type::String)])],
            title: 'Some movie',
        );

        Assert::equals($property->jsonSerialize(), [
            'type' => 'array',
            'title' => 'Some movie',
            'items' => [
                'type' => 'string',
            ],
        ]);
    }

    public function testPropertyWithArrayTypeMultipleElems(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Type::Array, collectionTypes: [new PropertyType(type: Movie::class), new PropertyType(type: Type::String)])],
            title: 'Some movie',
        );

        Assert::equals($property->jsonSerialize(), [
            'type' => 'array',
            'title' => 'Some movie',
            'items' => [
                'anyOf' => [
                    [
                        '$ref' => '#/definitions/Movie',
                    ],
                    [
                        'type' => 'string',
                    ],
                ],
            ],
        ]);
    }

    #[ExpectException(\InvalidArgumentException::class)]
    public function testInvalidTypeException(): void
    {
        new SimpleType(name: 'foo', builtin: true);
    }

    public function testPropertyWithFormat(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], title: 'Homepage', format: Format::Uri);

        Assert::equals($property->jsonSerialize(), [
            'type' => 'string',
            'title' => 'Homepage',
            'format' => 'uri',
        ]);
    }
}
