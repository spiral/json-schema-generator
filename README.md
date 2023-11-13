# JSON schema generator

[![PHP Version Require](https://poser.pugx.org/spiral/json-schema-generator/require/php)](https://packagist.org/packages/spiral/json-schema-generator)
[![Latest Stable Version](https://poser.pugx.org/spiral/json-schema-generator/v/stable)](https://packagist.org/packages/spiral/json-schema-generator)
[![phpunit](https://github.com/spiral/json-schema-generator/actions/workflows/phpunit.yml/badge.svg)](https://github.com/spiral/json-schema-generator/actions)
[![psalm](https://github.com/spiral/json-schema-generator/actions/workflows/psalm.yml/badge.svg)](https://github.com/spiral/json-schema-generator/actions)
[![Codecov](https://codecov.io/gh/spiral/json-schema-generator/branch/1.x/graph/badge.svg)](https://codecov.io/gh/spiral/json-schema-generator)
[![Total Downloads](https://poser.pugx.org/spiral/json-schema-generator/downloads)](https://packagist.org/packages/spiral/json-schema-generator)
[![type-coverage](https://shepherd.dev/github/spiral/json-schema-generator/coverage.svg)](https://shepherd.dev/github/spiral/json-schema-generator)
[![psalm-level](https://shepherd.dev/github/spiral/json-schema-generator/level.svg)](https://shepherd.dev/github/spiral/json-schema-generator)

The JSON Schema Generator is a powerful PHP package designed to simplify the process of generating JSON schemas
from Data Transfer Object (DTO) classes. It supports PHP enumerations, generic type annotations for arrays,
and provides an attribute for specifying title, description, and default value.

## Requirements

Make sure that your server is configured with following PHP version and extensions:

- PHP >=8.1

## Installation

You can install the package via composer:

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
> Additionally, the package provides the `Spiral\JsonSchemaGenerator\GeneratorInterface`, which can be seamlessly
> integrated into your application's dependency container for further customization and flexibility.

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
            'type'        => 'string',
        ],
        'director'      => [
            'type' => 'string',
        ],
        'releaseStatus' => [
            'title'       => 'Release Status',
            'description' => 'The release status of the movie',
            'allOf'       => [
                [
                    '$ref' => '#/definitions/ReleaseStatus',
                ],
            ],
        ],
    ],
    'required'    => [
        'title',
        'year',
    ],
    'definitions' => [
        'ReleaseStatus' => [
            'title' => 'ReleaseStatus',
            'type'  => 'string',
            'enum'  => [
                'Released',
                'Rumored',
                'Post Production',
                'In Production',
                'Planned',
                'Canceled',
            ],
        ],
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
        public readonly array $movies = [],
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
            'type'  => 'array',
            'items' => [
                '$ref' => '#/definitions/Movie',
            ],
            'default' => [],
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
                    'type'        => 'string',
                ],
                'director'      => [
                    'type' => 'string',
                ],
                'releaseStatus' => [
                    'title'       => 'Release Status',
                    'description' => 'The release status of the movie',
                    'allOf'       => [
                        [
                            '$ref' => '#/definitions/ReleaseStatus',
                        ],
                    ],
                ],
            ],
            'required'   => [
                'title',
                'year',
            ],
        ],
        'ReleaseStatus' => [
            'title' => 'ReleaseStatus',
            'type'  => 'string',
            'enum'  => [
                'Released',
                'Rumored',
                'Post Production',
                'In Production',
                'Planned',
                'Canceled',
            ],
       ]
    ],
];
```

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
