# JSON Schema Generator

[![PHP Version Require](https://poser.pugx.org/spiral/json-schema-generator/require/php)](https://packagist.org/packages/spiral/json-schema-generator)
[![Latest Stable Version](https://poser.pugx.org/spiral/json-schema-generator/v/stable)](https://packagist.org/packages/spiral/json-schema-generator)
[![phpunit](https://github.com/spiral/json-schema-generator/actions/workflows/phpunit.yml/badge.svg)](https://github.com/spiral/json-schema-generator/actions)
[![psalm](https://github.com/spiral/json-schema-generator/actions/workflows/psalm.yml/badge.svg)](https://github.com/spiral/json-schema-generator/actions)
[![Total Downloads](https://poser.pugx.org/spiral/json-schema-generator/downloads)](https://packagist.org/packages/spiral/json-schema-generator)
[![psalm-level](https://shepherd.dev/github/spiral/json-schema-generator/level.svg)](https://shepherd.dev/github/spiral/json-schema-generator)

The JSON Schema Generator is a PHP package that simplifies the generation of [JSON schemas](https://json-schema.org/) from Data Transfer Object (DTO) classes.
It supports PHP enumerations and generic type annotations for arrays and provides an attribute for specifying title, description, and default value.

Main use case - structured output definition for LLMs.

## Requirements

Make sure that your server is configured with the following PHP versions and extensions:

- PHP >=8.1

## Installation

You can install the package via Composer:

```bash
composer require spiral/json-schema-generator
```

## Usage

To generate a schema for a DTO, instantiate the `Spiral\JsonSchemaGenerator\Generator` and call the **generate** method,
passing the DTO class as an argument (fully qualified class name or reflection). The method will return an instance of
`Spiral\JsonSchemaGenerator\Schema`.

Let's create a simple data transfer object:

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;

class Movie
{
    public function __construct(
        #[Field(title: 'Title', description: 'The title of the movie')]
        public readonly string $title,
        #[Field(title: 'Year', description: 'The year of the movie')]
        public readonly int $year,
        #[Field(title: 'Description', description: 'The description of the movie')]
        public readonly ?string $description = null,
        public readonly ?string $director = null,
        #[Field(title: 'Release Status', description: 'The release status of the movie')]
        public readonly ?ReleaseStatus $releaseStatus = null,
    ) {
    }
}
```

This DTO has a **releaseStatus**, which is an enum. Let's create it:

```php
namespace App\DTO;

enum ReleaseStatus: string
{
    case Released = 'Released';
    case Rumored = 'Rumored';
    case PostProduction = 'Post Production';
    case InProduction = 'In Production';
    case Planned = 'Planned';
    case Canceled = 'Canceled';
}

```

Now, let's generate a schema for this DTO:

```php
use Spiral\JsonSchemaGenerator\Generator;
use App\DTO\Movie;

$generator = new Generator();
$schema = $generator->generate(Movie::class);
```

> **Note**
> Additionally, the package provides the `Spiral\JsonSchemaGenerator\GeneratorInterface,` which can be integrated into your application's dependency container for further customization and flexibility.

The `Spiral\JsonSchemaGenerator\Schema` object implements the **JsonSerializable** interface, allowing easy conversion
of the schema into either JSON or a PHP array.

Example array output:

```php
[
    'properties'  => [
        'title'         => [
            'title'       => 'Title',
            'description' => 'The title of the movie',
            'type'        => 'string',
        ],
        'year'          => [
            'title'       => 'Year',
            'description' => 'The year of the movie',
            'type'        => 'integer',
        ],
        'description'   => [
            'title'       => 'Description',
            'description' => 'The description of the movie',
            'oneOf'       => [
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
            'title'       => 'Release Status',
            'description' => 'The release status of the movie',
            'oneOf'       => [
                [
                    'type' => 'null',
                ],
                [
                    'type' => 'string',
                    'enum' => [
                        'Released',
                        'Rumored',
                        'Post Production',
                        'In Production',
                        'Planned',
                        'Canceled',
                    ],
                ],
            ],
        ],
    ],
    'required'    => [
        'title',
        'year',
    ],
];
```

Class properties can be arrays, and the type of elements within the array can be specified using PHPDoc annotations.

For example, we have a DTO with an array of objects:

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class Actor
{
    public function __construct(
        public readonly string $name,
        /**
         * @var array<Movie>
         */
        public readonly ?array $movies = null,
        public readonly ?Movie $bestMovie = null;
    ) {
    }
}
```

In this example, we use a PHPDoc block to indicate that the property **$movies** contains an array of **Movie** objects.

> **Note**
> Various documentation type annotations are supported, including `@var array<Movie>`, `@var Movie[]`,
> and `@var list<Movie>`. For promoted properties, you can use annotations like `@param array<Movie> $movies`,
> `@param Movie[] $movies`, and `@param list<Movie> $movies`.

Now, let's generate a schema for this DTO:

```php
use Spiral\JsonSchemaGenerator\Generator;
use App\DTO\Actor;

$generator = new Generator();
$schema = $generator->generate(Actor::class);
```

Example array output:

```php
[
    'properties' => [
        'name'   => [
            'type' => 'string',
        ],
        'movies' => [
            'oneOf' => [
                [
                    'type' => 'null',
                ],
                [
                    'type'  => 'array',
                    'items' => [
                        '$ref' => '#/definitions/Movie',
                    ],
                ],
            ],
        ],
        'bestMovie' => [
            'title'       => 'Best Movie',
            'description' => 'The best movie of the actor',
            'oneOf'       => [
                [
                    'type' => 'null',
                ],
                [
                    '$ref' => '#/definitions/Movie',
                ],
            ],
        ],
    ],
    'required'   => [
        'name',
    ],
    'definitions' => [
        'Movie'         => [
            'title'      => 'Movie',
            'type'       => 'object',
            'properties' => [
                'title'         => [
                    'title'       => 'Title',
                    'description' => 'The title of the movie',
                    'type'        => 'string',
                ],
                'year'          => [
                    'title'       => 'Year',
                    'description' => 'The year of the movie',
                    'type'        => 'integer',
                ],
                'description'   => [
                    'title'       => 'Description',
                    'description' => 'The description of the movie',
                    'oneOf'       => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                ],
                'director'      => [
                    'oneOf' => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                ],
                'releaseStatus' => [
                    'title'       => 'Release Status',
                    'description' => 'The release status of the movie',
                    'oneOf'       => [
                        ['type' => 'null'],
                        ['type' => 'string'],
                    ],
                    'enum'        => ['Released', 'Rumored', 'Post Production', 'In Production', 'Planned', 'Canceled'],
                ],
            ],
            'required'   => [
                'title',
                'year',
            ],
        ],
    ],
];
```

## Polymorphic Arrays (anyOf)
The generator also supports arrays that contain different types of DTOs using PHPDoc annotations like **@var list<Movie|Series>**.
For example, if an actor can have a filmography that includes both movies and TV series, you can define it like this:
```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class Actor
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,

        #[Field(title: 'Biography', description: 'The biography of the actor')]
        public readonly ?string $bio = null,

        /**
         * @var list<Movie|Series>|null
         */
        #[Field(title: 'Filmography', description: 'List of movies and series featuring the actor')]
        public readonly ?array $filmography = null,

        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public readonly ?Movie $bestMovie = null,

        #[Field(title: 'Best Series', description: 'The most prominent series of the actor')]
        public readonly ?Series $bestSeries = null,
    ) {}
}
```
The generated schema will reflect this with an anyOf definition in the items section:
```php
[
    'properties' => [
        'filmography' => [
            'title'       => 'Filmography',
            'description' => 'List of movies and series featuring the actor',
            'oneOf'       => [
                ['type' => 'null'],
                [
                    'type'  => 'array',
                    'items' => [
                        'anyOf' => [
                            ['$ref' => '#/definitions/Movie'],
                            ['$ref' => '#/definitions/Series'],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'definitions' => [
        'Movie'  => [/* ... */],
        'Series' => [/* ... */],
    ],
];
```
## Example DTO: Series
Here's what the Series class might look like:
```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\Format;

final class Series
{
    public function __construct(
        #[Field(title: 'Title', description: 'The title of the series')]
        public readonly string $title,

        #[Field(title: 'First Air Year', description: 'The year the series first aired')]
        public readonly int $firstAirYear,

        #[Field(title: 'Description', description: 'The description of the series')]
        public readonly ?string $description = null,

        #[Field(title: 'Creator', description: 'The creator or showrunner of the series')]
        public readonly ?string $creator = null,

        #[Field(title: 'Series Status', description: 'The current status of the series')]
        public readonly ?SeriesStatus $status = null,

        #[Field(title: 'First Air Date', description: 'The original release date of the series', format: Format::Date)]
        public readonly ?string $firstAirDate = null,

        #[Field(title: 'Last Air Date', description: 'The most recent air date of the series', format: Format::Date)]
        public readonly ?string $lastAirDate = null,

        #[Field(title: 'Seasons', description: 'Number of seasons released')]
        public readonly ?int $seasons = null,
    ) {}
}
```
> **Note**
> When using polymorphic arrays, make sure all referenced DTOs (e.g., Movie, Series)
> are also annotated properly so their definitions can be generated correctly.

## Union Types
The JSON Schema Generator supports native PHP union types (introduced in PHP 8.0), including nullable and multi-type definitions.
Here's an example DTO using union types:
```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class FlexibleValue
{
    public function __construct(
        #[Field(title: 'Value', description: 'Can be either string or integer')]
        public readonly string|int $value,

        #[Field(title: 'Optional Flag', description: 'Boolean or null')]
        public readonly bool|null $flag = null,

        #[Field(title: 'Flexible Field', description: 'Can be string, int, or null')]
        public readonly string|int|null $flex = null,
    ) {}
}
```
The generated schema will include a `oneOf` section to reflect the union types:
```php
[
    'properties' => [
        'value' => [
            'title'       => 'Value',
            'description' => 'Can be either string or integer',
            'oneOf'       => [
                ['type' => 'string'],
                ['type' => 'integer'],
            ],
        ],
        'flag' => [
            'title'       => 'Optional Flag',
            'description' => 'Boolean or null',
            'oneOf'       => [
                ['type' => 'null'],
                ['type' => 'boolean'],
            ],
        ],
        'flex' => [
            'title'       => 'Flexible Field',
            'description' => 'Can be string, int, or null',
            'oneOf'       => [
                ['type' => 'null'],
                ['type' => 'string'],
                ['type' => 'integer'],
            ],
        ],
    ],
    'required' => ['value'],
]
```
> **Note**
> All supported types are automatically resolved from native PHP type declarations and reflected in the JSON Schema output using oneOf.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
