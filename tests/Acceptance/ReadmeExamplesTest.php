<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance;

use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Actor;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\BlogPost;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\ContactInfo;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\DynamicConfig;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\FlexibleValue;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Movie;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Polymorphic\Actor as PolymorphicActor;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Product;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\User;
use Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\ValidatedUser;
use Testo\Assert;
use Testo\Test;

/**
 * The DTOs in `Readme/` are copied from README.md; each test checks that the schema README.md shows for them is
 * what the generator produces. Branch order inside `oneOf`/`anyOf` and key order are not significant.
 */
#[Test]
final class ReadmeExamplesTest
{
    private const array MOVIE_PROPERTIES = [
        'title' => [
            'title' => 'Title',
            'description' => 'The title of the movie',
            'type' => 'string',
        ],
        'year' => [
            'title' => 'Year',
            'description' => 'The year of the movie',
            'type' => 'integer',
        ],
        'description' => [
            'title' => 'Description',
            'description' => 'The description of the movie',
            'oneOf' => [
                ['type' => 'null'],
                ['type' => 'string'],
            ],
        ],
        'director' => [
            'oneOf' => [
                ['type' => 'null'],
                ['type' => 'string'],
            ],
        ],
        'releaseStatus' => [
            'title' => 'Release Status',
            'description' => 'The release status of the movie',
            'oneOf' => [
                ['type' => 'null'],
                [
                    'type' => 'string',
                    'enum' => ['Released', 'Rumored', 'Post Production', 'In Production', 'Planned', 'Canceled'],
                ],
            ],
        ],
    ];

    public function basicUsage(): void
    {
        self::assertSchema(Movie::class, [
            'type' => 'object',
            'properties' => self::MOVIE_PROPERTIES,
            'required' => ['title', 'year'],
        ]);
    }

    public function arrayTypeAnnotations(): void
    {
        self::assertSchema(Actor::class, [
            'type' => 'object',
            'properties' => [
                'name' => ['type' => 'string'],
                'movies' => [
                    'oneOf' => [
                        ['type' => 'null'],
                        [
                            'type' => 'array',
                            'items' => ['$ref' => '#/definitions/Movie'],
                        ],
                    ],
                ],
                'bestMovie' => [
                    'title' => 'Best Movie',
                    'description' => 'The best movie of the actor',
                    'oneOf' => [
                        ['type' => 'null'],
                        ['$ref' => '#/definitions/Movie'],
                    ],
                ],
            ],
            'required' => ['name'],
            'definitions' => [
                'Movie' => [
                    'title' => 'Movie',
                    'type' => 'object',
                    'properties' => self::MOVIE_PROPERTIES,
                    'required' => ['title', 'year'],
                ],
            ],
        ]);
    }

    public function polymorphicArrays(): void
    {
        $schema = self::generate(PolymorphicActor::class);

        Assert::same($schema['properties']['filmography'], self::normalize([
            'title' => 'Filmography',
            'description' => 'List of movies and series featuring the actor',
            'oneOf' => [
                ['type' => 'null'],
                [
                    'type' => 'array',
                    'items' => [
                        'anyOf' => [
                            ['$ref' => '#/definitions/Movie'],
                            ['$ref' => '#/definitions/Series'],
                        ],
                    ],
                ],
            ],
        ]));
        Assert::same(\array_keys($schema['definitions']), ['Movie', 'Series']);
    }

    public function unionTypes(): void
    {
        self::assertSchema(FlexibleValue::class, [
            'type' => 'object',
            'properties' => [
                'value' => [
                    'title' => 'Value',
                    'description' => 'Can be either string or integer',
                    'oneOf' => [
                        ['type' => 'string'],
                        ['type' => 'integer'],
                    ],
                ],
                'flag' => [
                    'title' => 'Optional Flag',
                    'description' => 'Boolean or null',
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'boolean'],
                    ],
                ],
                'flex' => [
                    'title' => 'Flexible Field',
                    'description' => 'Can be string, int, or null',
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                        ['type' => 'integer'],
                    ],
                ],
            ],
            'required' => ['value'],
        ]);
    }

    public function constraintAttributes(): void
    {
        Assert::same(self::generate(User::class)['properties']['name'], self::normalize([
            'title' => 'Full Name',
            'description' => 'User full name in Title Case',
            'type' => 'string',
            'pattern' => '^[A-Z][a-z]+(?: [A-Z][a-z]+)*$',
            'minLength' => 2,
            'maxLength' => 100,
        ]));
        Assert::same(self::generate(Product::class)['properties']['price'], self::normalize([
            'title' => 'Price',
            'description' => 'Product price in USD',
            'type' => 'number',
            'minimum' => 0.01,
            'maximum' => 99999.99,
            'multipleOf' => 0.01,
        ]));

        $blogPost = self::generate(BlogPost::class)['properties'];
        Assert::same($blogPost['tags'], self::normalize([
            'title' => 'Tags',
            'description' => 'Post tags',
            'type' => 'array',
            'minItems' => 1,
            'maxItems' => 10,
            'uniqueItems' => true,
        ]));
        Assert::same($blogPost['status'], self::normalize([
            'title' => 'Status',
            'type' => 'string',
            'enum' => ['draft', 'published', 'archived', 'pending'],
        ]));
    }

    public function phpDocValidationConstraints(): void
    {
        $schema = self::generate(ValidatedUser::class);

        Assert::same(\array_intersect_key($schema['properties'], \array_flip(['name', 'age', 'score', 'phone', 'tags', 'preferences'])), self::normalize([
            'name' => [
                'title' => 'Name',
                'description' => 'User full name',
                'type' => 'string',
                'minLength' => 1,
            ],
            'age' => [
                'title' => 'Age',
                'description' => 'User age',
                'type' => 'integer',
                'minimum' => 1,
            ],
            'score' => [
                'title' => 'Score',
                'description' => 'User score between 0 and 100',
                'type' => 'integer',
                'minimum' => 0,
                'maximum' => 100,
            ],
            'phone' => [
                'title' => 'Phone Number',
                'description' => 'Numeric phone number',
                'type' => 'string',
                'pattern' => '^[0-9]*\.?[0-9]+$',
            ],
            'tags' => [
                'title' => 'Tags',
                'description' => 'User tags',
                'type' => 'array',
                'items' => ['type' => 'string'],
                'minItems' => 1,
                'default' => [],
            ],
            'preferences' => [
                'title' => 'Preferences',
                'description' => 'User preferences',
                'type' => 'object',
                'properties' => [
                    'theme' => ['type' => 'string'],
                    'notifications' => ['type' => 'boolean'],
                ],
                'required' => ['theme', 'notifications'],
                'additionalProperties' => false,
                'default' => [],
            ],
        ]));
        Assert::same($schema['required'], ['name', 'age', 'score', 'email', 'phone']);
    }

    public function formatSupport(): void
    {
        $properties = self::generate(ContactInfo::class)['properties'];

        Assert::same(\array_map(static fn(array $property): string => $property['format'], $properties), self::normalize([
            'email' => 'email',
            'website' => 'uri',
            'birthDate' => 'date',
            'lastLogin' => 'date-time',
        ]));
    }

    public function additionalProperties(): void
    {
        $schema = self::generate(DynamicConfig::class);
        unset($schema['definitions']);

        Assert::same($schema, self::normalize([
            'type' => 'object',
            'properties' => [
                'name' => [
                    'title' => 'Config Name',
                    'description' => 'Name of the configuration set',
                    'type' => 'string',
                ],
                'version' => [
                    'title' => 'Version',
                    'description' => 'Configuration version',
                    'type' => 'integer',
                ],
                'settings' => [
                    'title' => 'Settings',
                    'description' => 'Dynamic configuration settings',
                    'type' => 'object',
                    'additionalProperties' => ['type' => 'string'],
                    'default' => [],
                ],
                'metadata' => [
                    'title' => 'Metadata',
                    'description' => 'Dynamic configuration metadata',
                    'type' => 'object',
                    'additionalProperties' => ['$ref' => '#/definitions/ValueObject'],
                    'default' => [],
                ],
            ],
            'required' => ['name', 'version'],
        ]));
    }

    public function additionalPropertiesValueObjectDefinition(): void
    {
        Assert::same(self::generate(DynamicConfig::class)['definitions']['ValueObject'], self::normalize([
            'title' => 'ValueObject',
            'type' => 'object',
            'properties' => [
                'label' => ['type' => 'string'],
                'value' => new \stdClass(),
            ],
            'required' => ['label', 'value'],
        ]));
    }

    /**
     * @param class-string $class
     */
    private static function assertSchema(string $class, array $expected): void
    {
        Assert::same(self::generate($class), self::normalize($expected));
    }

    /**
     * @param class-string $class
     */
    private static function generate(string $class): array
    {
        return self::normalize((new Generator())->generate($class));
    }

    /**
     * Decodes the schema the way a JSON consumer sees it and sorts what JSON Schema treats as unordered.
     */
    private static function normalize(mixed $schema): array
    {
        return self::sort(\json_decode(\json_encode($schema, \JSON_THROW_ON_ERROR), true, flags: \JSON_THROW_ON_ERROR));
    }

    private static function sort(mixed $value, ?string $key = null): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }

        foreach ($value as $k => $item) {
            $value[$k] = self::sort($item, \is_string($k) ? $k : null);
        }

        if (\array_is_list($value)) {
            if (\in_array($key, ['oneOf', 'anyOf'], true)) {
                \usort($value, static fn(mixed $a, mixed $b): int => \json_encode($a) <=> \json_encode($b));
            }
            return $value;
        }

        \ksort($value);
        return $value;
    }
}
