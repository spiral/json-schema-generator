<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Schema;

use PHPUnit\Framework\TestCase;
use Spiral\JsonSchemaGenerator\Exception\DefinitionException;
use Spiral\JsonSchemaGenerator\Schema\Definition;
use Spiral\JsonSchemaGenerator\Schema\Property;
use Spiral\JsonSchemaGenerator\Schema\Type;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\InvalidEnum;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\ReleaseStatus;

final class DefinitionTest extends TestCase
{
    public function testClassDefinition(): void
    {
        $definition = new Definition(
            type: Movie::class,
            properties: [
                'title' => new Property(
                    type: Type::String,
                    title: 'Title',
                    description: 'Title of the movie',
                    required: true,
                ),
                'status' => new Property(
                    type: ReleaseStatus::class,
                    title: 'Status',
                    description: 'Status of the movie',
                    required: true,
                ),
            ],
        );

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'title' => [
                    'title' => 'Title',
                    'description' => 'Title of the movie',
                    'type' => 'string',
                ],
                'status' => [
                    'title' => 'Status',
                    'description' => 'Status of the movie',
                    'allOf' => [
                        [
                            '$ref' => '#/definitions/ReleaseStatus',
                        ],
                    ],
                ],
            ],
            'required' => [
                'title',
                'status',
            ],
        ], $definition->jsonSerialize());
    }

    public function testEnumDefinition(): void
    {
        $definition = new Definition(
            type: ReleaseStatus::class,
            options: \array_map(
                static fn (ReleaseStatus $status): string => $status->value,
                ReleaseStatus::cases()
            ),
            title: 'status',
        );

        $this->assertEquals([
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
        ], $definition->jsonSerialize());
    }

    public function testInvalidPropertyException(): void
    {
        $this->expectException(DefinitionException::class);
        new Definition(type: Movie::class, properties: ['foo']);
    }

    public function testInvalidClassException(): void
    {
        $this->expectException(DefinitionException::class);
        (new Definition(type: \stdClass::class))->jsonSerialize();
    }

    public function testInvalidEnumException(): void
    {
        $this->expectException(DefinitionException::class);
        (new Definition(type: InvalidEnum::class))->jsonSerialize();
    }
}
