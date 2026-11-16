<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use Spiral\JsonSchemaGenerator\Exception\DefinitionException;
use Spiral\JsonSchemaGenerator\Schema\Definition;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Schema\PropertyType;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\InvalidEnum;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Priority;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ReleaseStatus;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Test;

#[Test]
final class DefinitionTest
{
    public function testClassDefinition(): void
    {
        $definition = new Definition(
            type: Movie::class,
            properties: [
                'title' => new Property(
                    types: [new PropertyType(type: Type::String)],
                    title: 'Title',
                    description: 'Title of the movie',
                    required: true,
                ),
                'status' => new Property(
                    types: [new PropertyType(type: Type::Null), new PropertyType(type: Type::String, enum: ['Released', 'Rumored', 'Post Production', 'In Production', 'Planned', 'Canceled'])],
                    title: 'Status',
                    description: 'Status of the movie',
                    required: true,
                ),
            ],
        );

        Assert::equals($definition->jsonSerialize(), [
            'type' => 'object',
            'properties' => [
                'title' => [
                    'title'       => 'Title',
                    'description' => 'Title of the movie',
                    'type'        => 'string',
                ],
                'status' => [
                    'title'       => 'Status',
                    'description' => 'Status of the movie',
                    'oneOf'       => [
                        ['type' => 'null'],
                        [
                            'type' => 'string',
                            'enum' => ['Released', 'Rumored', 'Post Production', 'In Production', 'Planned', 'Canceled'],
                        ],
                    ],
                ],
            ],
            'required' => [
                'title',
                'status',
            ],
        ]);
    }

    public function testEnumDefinition(): void
    {
        $definition = new Definition(
            type: ReleaseStatus::class,
            options: \array_map(
                static fn(ReleaseStatus $status): string => $status->value,
                ReleaseStatus::cases(),
            ),
            title: 'status',
        );

        Assert::equals($definition->jsonSerialize(), [
            'title' => 'status',
            'type' => 'string',
            'enum' => [
                'Released',
                'Rumored',
                'Post Production',
                'In Production',
                'Planned',
                'Canceled',
            ],
        ]);
    }

    #[ExpectException(DefinitionException::class)]
    public function testInvalidPropertyException(): void
    {
        new Definition(type: Movie::class, properties: ['foo']);
    }

    #[ExpectException(DefinitionException::class)]
    public function testInvalidClassException(): void
    {
        (new Definition(type: \stdClass::class))->jsonSerialize();
    }

    #[ExpectException(DefinitionException::class)]
    public function testInvalidEnumException(): void
    {
        (new Definition(type: InvalidEnum::class))->jsonSerialize();
    }

    public function testIntBackedEnumDefinition(): void
    {
        $definition = new Definition(
            type: Priority::class,
            options: [1, 2],
            title: 'Priority',
            description: 'Task priority',
        );

        Assert::same($definition->jsonSerialize(), [
            'title' => 'Priority',
            'description' => 'Task priority',
            'type' => 'number',
            'enum' => [1, 2],
        ]);
    }

    public function testSimpleTypeDefinition(): void
    {
        Assert::same((new Definition(type: Type::String))->jsonSerialize(), ['type' => 'string']);
    }
}
