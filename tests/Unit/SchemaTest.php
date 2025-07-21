<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Schema;
use Spiral\JsonSchemaGenerator\Schema\PropertyType;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;

final class SchemaTest extends TestCase
{
    public function testEmpty(): void
    {
        $schema = new Schema();

        $this->assertSame(['type' => 'object'], $schema->jsonSerialize());
    }

    public function testStringProperty(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            'name',
            new Schema\Property(
                types: [new PropertyType(type: Schema\Type::String)],
                title: 'Name',
                description: 'Name of the user',
                required: true,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'name' => [
                        'title' => 'Name',
                        'description' => 'Name of the user',
                        'type' => 'string',
                    ],
                ],
                'required'   => [
                    'name',
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testScalarProperties(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'name',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::String)],
                title: 'Name',
                description: 'Name of the user',
                required: true,
            ),
        );

        $schema->addProperty(
            name: 'age',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Integer)],
                title: 'Age',
                description: 'Age of the user',
                required: true,
            ),
        );

        $schema->addProperty(
            name: 'height',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Number)],
                title: 'Height',
                description: 'Height of the user',
                required: true,
            ),
        );

        $schema->addProperty(
            name: 'is_active',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Boolean)],
                title: 'Is Active',
                description: 'Is the user active',
                required: false,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'name'      => [
                        'title'       => 'Name',
                        'description' => 'Name of the user',
                        'type'        => 'string',
                    ],
                    'age'       => [
                        'title'       => 'Age',
                        'description' => 'Age of the user',
                        'type'        => 'integer',
                    ],
                    'height'    => [
                        'title'       => 'Height',
                        'description' => 'Height of the user',
                        'type'        => 'number',
                    ],
                    'is_active' => [
                        'title'       => 'Is Active',
                        'description' => 'Is the user active',
                        'type'        => 'boolean',
                    ],
                ],
                'required'   => [
                    'name',
                    'age',
                    'height',
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testArrayProperty(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'hobbies',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Array, collectionTypes: [new PropertyType(type: Schema\Type::String)])],
                title: 'Hobbies',
                description: 'Hobbies of the user',
                required: true,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'hobbies' => [
                        'type'        => 'array',
                        'items'       => [
                            'type' => 'string',
                        ],
                        'title'       => 'Hobbies',
                        'description' => 'Hobbies of the user',
                    ],
                ],
                'required'   => [
                    'hobbies',
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testArrayPropertyWithMultipleTypes(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'hobbies',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Array, collectionTypes: [new PropertyType(type: Schema\Type::String), new PropertyType(type: Schema\Type::Number)])],
                title: 'Hobbies',
                description: 'Hobbies of the user',
                required: true,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'hobbies' => [
                        'type'        => 'array',
                        'items'       => [
                            'anyOf' => [
                                ['type' => 'string'],
                                ['type' => 'number'],
                            ],
                        ],
                        'title'       => 'Hobbies',
                        'description' => 'Hobbies of the user',
                    ],
                ],
                'required'   => [
                    'hobbies',
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testClassProperty(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'movie',
            property: new Schema\Property(
                types: [new PropertyType(type: Movie::class)],
                title: 'Some movie',
                required: false,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'movie' => [
                        'title' => 'Some movie',
                        '$ref' => '#/definitions/Movie',
                    ],
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testClassArrayProperty(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'movie',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Array, collectionTypes: [new PropertyType(type: Movie::class)])],
                title: 'Some movie',
                required: false,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'movie' => [
                        'title' => 'Some movie',
                        'type'  => 'array',
                        'items' => [
                            '$ref' => '#/definitions/Movie',
                        ],
                    ],
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testArrayOfClassesAndStrings(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'movie',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Array, collectionTypes: [new PropertyType(type: Movie::class), new PropertyType(type: Schema\Type::String)])],
                title: 'Some movie',
                required: false,
            ),
        );

        $this->assertEquals(
            [
                'type' => 'object',
                'properties' => [
                    'movie' => [
                        'title' => 'Some movie',
                        'type'  => 'array',
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
                    ],
                ],
            ],
            $schema->jsonSerialize(),
        );
    }

    public function testNestedDefinition(): void
    {
        $schema = new Schema();
        $schema->addProperty(
            name: 'movie',
            property: new Schema\Property(
                types: [new PropertyType(type: Schema\Type::Array, collectionTypes: [new PropertyType(type: Movie::class), new PropertyType(type: Schema\Type::String)])],
                title: 'Some movie',
                required: false,
            ),
        );

        $definition = new Schema\Definition(
            type: Movie::class,
            properties: [
                'title' => new Schema\Property(
                    types: [new PropertyType(type: Schema\Type::String)],
                    title: 'Title',
                    description: 'Title of the movie',
                    required: true,
                ),
            ],
        );

        $schema->addDefinition('Movie', $definition);

        $this->assertEquals(
            [
                'type' => 'object',
                'properties'  => [
                    'movie' => [
                        'title' => 'Some movie',
                        'type'  => 'array',
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
                    ],
                ],
                'definitions' => [
                    'Movie' => [
                        'type'       => 'object',
                        'properties' => [
                            'title' => [
                                'type'        => 'string',
                                'title'       => 'Title',
                                'description' => 'Title of the movie',
                            ],
                        ],
                        'required'   => [
                            'title',
                        ],
                    ],
                ],
            ],
            $schema->jsonSerialize(),
        );
    }
}
