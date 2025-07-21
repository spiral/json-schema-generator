<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Parser\SimpleType;
use Spiral\JsonSchemaGenerator\Schema\Format;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Schema\PropertyType;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Actor;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class PropertyTest extends TestCase
{
    public function testPropertyOnlyRequiredParams(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)]);

        $this->assertEquals(['type' => 'string'], $property->jsonSerialize());
    }

    public function testPropertyWithTitle(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], title: 'Some movie');

        $this->assertEquals([
            'type' => 'string',
            'title' => 'Some movie',
        ], $property->jsonSerialize());
    }

    public function testPropertyWithDescription(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], description: 'Some description');

        $this->assertEquals([
            'type' => 'string',
            'description' => 'Some description',
        ], $property->jsonSerialize());
    }

    public function testPropertyWithDefault(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], default: 'value');

        $this->assertEquals([
            'type' => 'string',
            'default' => 'value',
        ], $property->jsonSerialize());
    }

    public function testPropertyWithUnionType(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Movie::class), new PropertyType(type: Actor::class)],
        );

        $this->assertEquals([
            'oneOf' => [
                [
                    '$ref' => '#/definitions/Movie',
                ],
                [
                    '$ref' => '#/definitions/Actor',
                ],
            ],
        ], $property->jsonSerialize());
    }

    public function testPropertyWithClassType(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Movie::class)],
        );

        $this->assertEquals([
            '$ref' =>  '#/definitions/Movie',
        ], $property->jsonSerialize());
    }

    public function testPropertyWithArrayTypeSingleClassElem(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Type::Array, collectionTypes: [new PropertyType(type: Movie::class)])],
            title: 'Some movie',
        );

        $this->assertEquals([
            'type' => 'array',
            'title' => 'Some movie',
            'items' => [
                '$ref' => '#/definitions/Movie',
            ],
        ], $property->jsonSerialize());
    }

    public function testPropertyWithArrayTypeSingleScalarElem(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Type::Array, collectionTypes: [new PropertyType(type: Type::String)])],
            title: 'Some movie',
        );

        $this->assertEquals([
            'type' => 'array',
            'title' => 'Some movie',
            'items' => [
                'type' => 'string',
            ],
        ], $property->jsonSerialize());
    }

    public function testPropertyWithArrayTypeMultipleElems(): void
    {
        $property = new Property(
            types: [new PropertyType(type: Type::Array, collectionTypes: [new PropertyType(type: Movie::class), new PropertyType(type: Type::String)])],
            title: 'Some movie',
        );

        $this->assertEquals([
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
        ], $property->jsonSerialize());
    }

    public function testInvalidTypeException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new SimpleType(name: 'foo', builtin: true);
    }

    public function testPropertyWithFormat(): void
    {
        $property = new Property(types: [new PropertyType(type: Type::String)], title: 'Homepage', format: Format::Uri);

        $this->assertEquals([
            'type' => 'string',
            'title' => 'Homepage',
            'format' => 'uri',
        ], $property->jsonSerialize());
    }
}
