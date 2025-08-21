# JSON Schema Generator

[![PHP Version Require](https://poser.pugx.org/spiral/json-schema-generator/require/php)](https://packagist.org/packages/spiral/json-schema-generator)
[![Latest Stable Version](https://poser.pugx.org/spiral/json-schema-generator/v/stable)](https://packagist.org/packages/spiral/json-schema-generator)
[![phpunit](https://github.com/spiral/json-schema-generator/actions/workflows/phpunit.yml/badge.svg)](https://github.com/spiral/json-schema-generator/actions)
[![psalm](https://github.com/spiral/json-schema-generator/actions/workflows/psalm.yml/badge.svg)](https://github.com/spiral/json-schema-generator/actions)
[![Total Downloads](https://poser.pugx.org/spiral/json-schema-generator/downloads)](https://packagist.org/packages/spiral/json-schema-generator)
[![psalm-level](https://shepherd.dev/github/spiral/json-schema-generator/level.svg)](https://shepherd.dev/github/spiral/json-schema-generator)

## Overview

The JSON Schema Generator is a PHP package that simplifies the generation of [JSON schemas](https://json-schema.org/)
from Data Transfer Object (DTO) classes.

**Main use case**: Structured output definition for LLMs.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Class Properties and Enums](#class-properties-and-enums)
- [Array Type Annotations](#array-type-annotations)
- [Polymorphic Arrays (anyOf)](#polymorphic-arrays-anyof)
- [Union Types](#union-types)
- [Constraint Attributes](#constraint-attributes)
- [PHPDoc Validation Constraints](#phpdoc-validation-constraints)
- [Format Support](#format-support)
- [Additional Properties](#additional-properties)
- [Configuration Options](#configuration-options)
- [Integration with Valinor](#integration-with-valinor)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Requirements

Make sure that your server is configured with the following PHP versions and extensions:

- PHP >=8.3

## Installation

You can install the package via Composer:

```bash
composer require spiral/json-schema-generator
```

## Basic Usage

Let's create a simple DTO with an enum:

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

To generate a schema for a DTO, instantiate the `Spiral\JsonSchemaGenerator\Generator` and call the **generate** method,
passing the DTO class as an argument (fully qualified class name or reflection). The method will return an instance of
`Spiral\JsonSchemaGenerator\Schema`.

```php
use Spiral\JsonSchemaGenerator\Generator;
use App\DTO\Movie;

$generator = new Generator();
$schema = $generator->generate(Movie::class);

// Convert to JSON
$jsonSchema = json_encode($schema, JSON_PRETTY_PRINT);

// Or use as array
$arraySchema = $schema->jsonSerialize();
```

> **Note**
> The package provides the `Spiral\JsonSchemaGenerator\GeneratorInterface,` which can be integrated into
> your application's dependency container for further customization and flexibility.


The generated schema for this DTO would include the following structure:

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

## Array Type Annotations

The generator supports arrays of objects with type information from PHPDoc annotations:

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
        
        #[Field(title: 'Best Movie', description: 'The best movie of the actor')]
        public readonly ?Movie $bestMovie = null,
    ) {
    }
}
```

> **Note**
> Various documentation type annotations are supported:
> - `@var array<Movie>`
> - `@var Movie[]`
> - `@var list<Movie>`
>
> For constructor-promoted properties, you can use annotations like:
> - `@param array<Movie> $movies`
> - `@param Movie[] $movies`
> - `@param list<Movie> $movies`

Generated schema (simplified):

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
                ['type' => 'null'],
                ['$ref' => '#/definitions/Movie'],
            ],
        ],
    ],
    'required'   => ['name'],
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

The generator supports arrays that contain different types of DTOs using PHPDoc annotations like
`@var list<Movie|Series>`:

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class Actor
{
    public function __construct(
        public readonly string $name,
        
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

The generated schema will include an `anyOf` definition in the items section:

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
        'Movie'  => [/* Movie schema definition */],
        'Series' => [/* Series schema definition */],
    ],
];
```

Here's what the Series class might look like:

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

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

enum SeriesStatus: string
{
    case Running = 'Running';
    case Ended = 'Ended';
    case Canceled = 'Canceled';
    case OnHiatus = 'On Hiatus';
}
```

> **Note**
> When using polymorphic arrays, make sure all referenced DTOs are properly annotated so their definitions can be
> generated correctly.

## Union Types

The JSON Schema Generator supports native PHP union types (introduced in PHP 8.0), including nullable and multi-type
definitions:

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

## Constraint Attributes

Generator supports dedicated constraint attributes that provide a clean, modular approach to validation rules.

### Available Constraint Attributes

#### String Constraints

- `#[Pattern(regex)]` - Regular expression pattern validation
- `#[Length(min, max)]` - String length constraints

#### Numeric Constraints

- `#[Range(min, max, exclusiveMin, exclusiveMax)]` - Numeric range validation with optional exclusive bounds
- `#[MultipleOf(value)]` - Multiple of validation for numbers

#### Array Constraints

- `#[Items(min, max, unique)]` - Array item constraints with optional uniqueness
- `#[Length(min, max)]` - Array length constraints (same attribute as strings, auto-detects type)

#### General Constraints

- `#[Enum(values)]` - Enumeration validation with array of allowed values

### Usage Examples

#### String Validation

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Pattern;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Schema\Format;

final readonly class User
{
    public function __construct(
        #[Field(title: 'Full Name', description: 'User full name in Title Case')]
        #[Pattern('^[A-Z][a-z]+(?: [A-Z][a-z]+)*$')]
        #[Length(min: 2, max: 100)]
        public string $name,

        #[Field(title: 'Username')]
        #[Pattern('^[a-zA-Z0-9_]{3,20}$')]
        #[Length(min: 3, max: 20)]
        public string $username,

        #[Field(title: 'Email', format: Format::Email)]
        #[Pattern('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$')]
        public string $email,
    ) {}
}
```

#### Numeric Validation

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Range;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\MultipleOf;

final readonly class Product
{
    public function __construct(
        #[Field(title: 'Price', description: 'Product price in USD')]
        #[Range(min: 0.01, max: 99999.99)]
        #[MultipleOf(0.01)]
        public float $price,

        #[Field(title: 'Stock Quantity')]
        #[Range(min: 0, max: 10000)]
        public int $stock,

        #[Field(title: 'Discount Percentage')]
        #[Range(min: 0, max: 100, exclusiveMax: true)]
        public float $discountPercent,
    ) {}
}
```

#### Array and Enum Validation

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Items;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Length;
use Spiral\JsonSchemaGenerator\Attribute\Constraint\Enum;

final readonly class BlogPost
{
    public function __construct(
        #[Field(title: 'Tags', description: 'Post tags')]
        #[Items(min: 1, max: 10, unique: true)]
        public array $tags,

        #[Field(title: 'Categories', description: 'Post categories')]
        #[Length(min: 1, max: 5)]
        public array $categories,

        #[Field(title: 'Status')]
        #[Enum(['draft', 'published', 'archived', 'pending'])]
        public string $status,

        #[Field(title: 'Priority')]
        #[Enum([1, 2, 3, 4, 5])]
        public int $priority,
    ) {}
}
```

### Generated Schema Output

The constraint attributes generate clean, standards-compliant JSON Schema validation rules:

```json
{
  "type": "object",
  "properties": {
    "name": {
      "title": "Full Name",
      "description": "User full name in Title Case",
      "type": "string",
      "pattern": "^[A-Z][a-z]+(?: [A-Z][a-z]+)*$",
      "minLength": 2,
      "maxLength": 100
    },
    "price": {
      "title": "Price",
      "description": "Product price in USD",
      "type": "number",
      "minimum": 0.01,
      "maximum": 99999.99,
      "multipleOf": 0.01
    },
    "tags": {
      "title": "Tags",
      "description": "Post tags",
      "type": "array",
      "minItems": 1,
      "maxItems": 10,
      "uniqueItems": true
    },
    "status": {
      "title": "Status",
      "type": "string",
      "enum": [
        "draft",
        "published",
        "archived",
        "pending"
      ]
    }
  },
  "required": [
    "name",
    "price",
    "tags",
    "status"
  ]
}
```

### Type Safety

Constraint attributes are automatically validated for type compatibility:

- `Pattern` only applies to string properties
- `Range` and `MultipleOf` only apply to numeric properties (int, float)
- `Items` constraints only apply to array properties
- `Length` adapts behavior: `minLength`/`maxLength` for strings, `minItems`/`maxItems` for arrays
- `Enum` works with any property type

## PHPDoc Validation Constraints

Generator supports extracting validation constraints from PHPDoc comments, providing rich validation
rules directly in your generated schemas.

### Supported PHPDoc Constraints

#### Numeric Constraints

- `positive-int` - Integer greater than 0
- `negative-int` - Integer less than 0
- `non-positive-int` - Integer less than or equal to 0
- `non-negative-int` - Integer greater than or equal to 0
- `int<min, max>` - Integer within a specific range

#### String Constraints

- `non-empty-string` - String with minimum length of 1
- `numeric-string` - String containing only numeric characters
- `class-string` - Valid PHP class name string

#### Array Constraints

- `non-empty-array` - Array with at least one element
- `non-empty-list` - List with at least one element
- `array{key: type, ...}` - Shaped arrays with specific structure

### Example Usage

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;

final class ValidatedUser
{
    public function __construct(
        #[Field(title: 'Name', description: 'User full name')]
        /** @var non-empty-string */
        public readonly string $name,
        
        #[Field(title: 'Age', description: 'User age')]
        /** @var positive-int */
        public readonly int $age,
        
        #[Field(title: 'Score', description: 'User score between 0 and 100')]
        /** @var int<0, 100> */
        public readonly int $score,
        
        #[Field(title: 'Email', description: 'User email address')]
        /** @var non-empty-string */
        public readonly string $email,
        
        #[Field(title: 'Phone Number', description: 'Numeric phone number')]
        /** @var numeric-string */
        public readonly string $phone,
        
        #[Field(title: 'Tags', description: 'User tags')]
        /** @var non-empty-array<string> */
        public readonly array $tags = [],
        
        #[Field(title: 'Preferences', description: 'User preferences')]
        /** @var array{theme: string, notifications: bool} */
        public readonly array $preferences = [],
    ) {}
}
```

The generated schema will include validation constraints:

```php
[
    'properties' => [
        'name' => [
            'title' => 'Name',
            'description' => 'User full name', 
            'type' => 'string',
            'minLength' => 1, // from non-empty-string
        ],
        'age' => [
            'title' => 'Age',
            'description' => 'User age',
            'type' => 'integer', 
            'minimum' => 1, // from positive-int
        ],
        'score' => [
            'title' => 'Score',
            'description' => 'User score between 0 and 100',
            'type' => 'integer',
            'minimum' => 0, // from int<0, 100>
            'maximum' => 100,
        ],
        'phone' => [
            'title' => 'Phone Number', 
            'description' => 'Numeric phone number',
            'type' => 'string',
            'pattern' => '^[0-9]*\.?[0-9]+$', // from numeric-string
        ],
        'tags' => [
            'title' => 'Tags',
            'description' => 'User tags', 
            'type' => 'array',
            'items' => ['type' => 'string'],
            'minItems' => 1, // from non-empty-array
            'default' => [],
        ],
        'preferences' => [
            'title' => 'Preferences',
            'description' => 'User preferences',
            'type' => 'object', // from array-shape constraint
            'properties' => [
                'theme' => ['type' => 'string'],
                'notifications' => ['type' => 'boolean'],
            ],
            'required' => ['theme', 'notifications'],
            'additionalProperties' => false,
            'default' => [],
        ],
    ],
    'required' => ['name', 'age', 'score', 'email', 'phone'],
]
```

## Format Support

The generator supports JSON Schema format validation through the `Format` enum:

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final class ContactInfo  
{
    public function __construct(
        #[Field(title: 'Email', description: 'User email address', format: Format::Email)]
        public readonly string $email,
        
        #[Field(title: 'Website', description: 'Personal website', format: Format::Uri)]
        public readonly ?string $website = null,
        
        #[Field(title: 'Birth Date', description: 'Date of birth', format: Format::Date)]
        public readonly ?string $birthDate = null,
        
        #[Field(title: 'Last Login', description: 'Last login timestamp', format: Format::DateTime)]
        public readonly ?string $lastLogin = null,
    ) {}
}
```

### Available Formats

- `Format::Date` - Date format (YYYY-MM-DD)
- `Format::Time` - Time format (HH:MM:SS)
- `Format::DateTime` - Date-time format (ISO 8601)
- `Format::Duration` - Duration format
- `Format::Email` - Email address format
- `Format::Hostname` - Hostname format
- `Format::Ipv4` - IPv4 address format
- `Format::Ipv6` - IPv6 address format
- `Format::Uri` - URI format
- `Format::UriReference` - URI reference format
- `Format::Uuid` - UUID format
- `Format::Regex` - Regular expression format

## Additional Properties

The generator supports defining additional properties for object types using the `AdditionalProperties` attribute. This
is useful for creating dynamic objects with a specific property type.

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;

final class DynamicConfig
{
    public function __construct(
        #[Field(title: 'Config Name', description: 'Name of the configuration set')]
        public readonly string $name,
        
        #[Field(title: 'Version', description: 'Configuration version')]
        public readonly int $version,
        
        /**
         * Dynamic settings map that can contain any string values
         */
        #[Field(title: 'Settings', description: 'Dynamic configuration settings')]
        #[AdditionalProperties(valueType: 'string')]
        public readonly array $settings = [],
        
        /**
         * Dynamic metadata with nested ValueObject instances
         */
        #[Field(title: 'Metadata', description: 'Dynamic configuration metadata')]
        #[AdditionalProperties(valueType: 'object', valueClass: ValueObject::class)]
        public readonly array $metadata = [],
    ) {}
}

final class ValueObject
{
    public function __construct(
        public readonly string $label,
        public readonly mixed $value,
    ) {}
}
```

The generated schema will include `additionalProperties` definitions:

```php
[
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
            'additionalProperties' => [
                'type' => 'string',
            ],
        ],
        'metadata' => [
            'title' => 'Metadata',
            'description' => 'Dynamic configuration metadata',
            'type' => 'object',
            'additionalProperties' => [
                '$ref' => '#/definitions/ValueObject',
            ],
        ],
    ],
    'required' => ['name', 'version'],
    'definitions' => [
        'ValueObject' => [
            'type' => 'object',
            'properties' => [
                'label' => ['type' => 'string'],
                'value' => ['type' => 'string'],
            ],
            'required' => ['label', 'value'],
        ],
    ],
]
```

### Supported Value Types

The `AdditionalProperties` attribute supports the following value types:

- Basic types: `'string'`, `'integer'`, `'number'`, `'boolean'`
- Object type: `'object'` (requires `valueClass` parameter for class references)
- Any type: `'mixed'` (translates to `additionalProperties: true`)

### Example with Multiple Dynamic Property Types

```php
namespace App\DTO;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;

final class ApiResponse
{
    public function __construct(
        public readonly bool $success,
        
        #[Field(title: 'Data', description: 'API response data with any structure')]
        #[AdditionalProperties(valueType: 'mixed')]
        public readonly array $data = [],
        
        #[Field(title: 'Errors', description: 'Error messages by field name')]
        #[AdditionalProperties(valueType: 'string')]
        public readonly array $errors = [],
        
        #[Field(title: 'Meta', description: 'Response metadata')]
        #[AdditionalProperties(valueType: 'object', valueClass: MetaValue::class)]
        public readonly array $meta = [],
    ) {}
}
```

## Configuration Options

```php
use Spiral\JsonSchemaGenerator\Generator;
use Spiral\JsonSchemaGenerator\Validation\AttributeConstraintExtractor;
use Spiral\JsonSchemaGenerator\Validation\PhpDocValidationConstraintExtractor;
use Spiral\JsonSchemaGenerator\Validation\CompositePropertyDataExtractor;
use Spiral\JsonSchemaGenerator\Validation\AdditionalPropertiesExtractor;

// Use default extractors (recommended for most cases)
$generator = new Generator(
    propertyDataExtractor: CompositePropertyDataExtractor::createDefault(),
);

// Advanced configuration - custom property data extractors
$compositeExtractor = new CompositePropertyDataExtractor([
    new PhpDocValidationConstraintExtractor(),
    new AttributeConstraintExtractor(),
    new AdditionalPropertiesExtractor(),
]);

$generator = new Generator(
    propertyDataExtractor: $compositeExtractor,
);
```

### Property Data Extractors

The generator uses a modular property data extractor system that allows you to customize how validation constraints are
extracted from properties:

**Available Extractors:**

- `PhpDocValidationConstraintExtractor` - Extracts constraints from PHPDoc comments
- `AttributeConstraintExtractor` - Extracts constraints from PHP attributes
- `AdditionalPropertiesExtractor` - Processes additional properties settings
- `CompositePropertyDataExtractor` - Combines multiple extractors

**Usage Examples:**

```php
// Use only PHPDoc constraints
$generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([
    new PhpDocValidationConstraintExtractor(),
]));

// Use only attribute constraints
$generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([
    new AttributeConstraintExtractor(),
]));

// Use both (default behavior)
$generator = new Generator(propertyDataExtractor: CompositePropertyDataExtractor::createDefault());

// Disable all validation constraints for performance
$generator = new Generator(propertyDataExtractor: new CompositePropertyDataExtractor([]));
```

### Custom Property Data Extractors

You can create custom property data extractors by implementing the `PropertyDataExtractorInterface`:

```php
use Spiral\JsonSchemaGenerator\Validation\PropertyDataExtractorInterface;
use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Schema\Type;

class CustomConstraintExtractor implements PropertyDataExtractorInterface
{
    public function extractValidationRules(PropertyInterface $property, Type $jsonSchemaType): array
    {
        $rules = [];
        
        // Your custom constraint extraction logic here
        // For example, extract constraints from custom attributes or naming conventions
        
        return $rules;
    }
}

// Use your custom extractor
$generator = new Generator(
    propertyDataExtractor: CompositePropertyDataExtractor::createDefault()
        ->withExtractor(new CustomConstraintExtractor())
);
```

## Integration with Valinor

The JSON Schema Generator works perfectly with the [Valinor PHP package](https://github.com/CuyZ/Valinor) for complete
data mapping and validation workflows. Valinor can validate incoming data based on the same PHPDoc constraints that the
generator uses to create JSON schemas.

### Installation

First, install Valinor alongside the JSON Schema Generator:

```bash
composer require cuyz/valinor spiral/json-schema-generator
```

### Complete Schema and Mapping Solution

Here's a complete example showing how to combine both packages:

```php
<?php

declare(strict_types=1);

namespace App;

use CuyZ\Valinor\Mapper\TreeMapper;
use Spiral\JsonSchemaGenerator\Generator;

final readonly class SchemaMapper
{
    public function __construct(
        private Generator $generator,
        private TreeMapper $mapper,
    ) {}

    public function toJsonSchema(string $class): array
    {
        if (\json_validate($class)) {
            return \json_decode($class, associative: true);
        }

        if (\class_exists($class)) {
            return $this->generator->generate($class)->jsonSerialize();
        }

        throw new \InvalidArgumentException(\sprintf('Invalid class or JSON schema provided: %s', $class));
    }

    /**
     * @template T of object
     * @param class-string<T>|null $class
     * @return T
     */
    public function toObject(string $json, ?string $class = null): object
    {
        if ($class === null) {
            return \json_decode($json, associative: false);
        }

        return $this->mapper->map($class, \json_decode($json, associative: true));
    }
}
```

### Usage Example

```php
use CuyZ\Valinor\MapperBuilder;
use Spiral\JsonSchemaGenerator\Generator;

// Set up the mapper with flexible casting and permissive types
$treeMapper = (new MapperBuilder())
    ->enableFlexibleCasting()
    ->allowPermissiveTypes()
    ->build();

$mapper = new SchemaMapper(
    generator: new Generator(), 
    mapper: $treeMapper
);

// Generate JSON schema for your DTO
$schema = $mapper->toJsonSchema(ValidatedUser::class);

// Convert incoming JSON to validated DTO
$payload = $request->getBody();
$user = $mapper->toObject($payload, ValidatedUser::class);
```

### Benefits of This Integration

1. **Consistent Validation**: Both packages respect the same PHPDoc validation constraints
2. **Schema Generation**: Generate JSON schemas for API documentation or LLM structured output
3. **Data Mapping**: Safely convert incoming JSON data to strongly-typed PHP DTOs
4. **Runtime Validation**: Valinor validates data against the same constraints used in schema generation
5. **Error Handling**: Get detailed validation errors when data doesn't match your DTO structure

### Real-world Example

```php
use App\DTO\ValidatedUser;

// Your DTO with PHPDoc constraints
final readonly class ValidatedUser
{
    public function __construct(
        /** @var non-empty-string */
        public string $name,
        /** @var positive-int */
        public int $age,
        /** @var int<0, 100> */
        public int $score,
    ) {}
}

// Generate schema (e.g., for OpenAPI documentation)
$schema = $mapper->toJsonSchema(ValidatedUser::class);
// Returns JSON schema with minLength, minimum constraints, etc.

// Validate and map incoming data
$jsonPayload = '{"name": "John Doe", "age": 25, "score": 85}';
$user = $mapper->toObject($jsonPayload, ValidatedUser::class);
// Returns ValidatedUser instance or throws validation exception

// Invalid data example
$invalidPayload = '{"name": "", "age": -5, "score": 150}';
$user = $mapper->toObject($invalidPayload, ValidatedUser::class);
// Throws validation exception: empty name, negative age, score out of range
```

### API Endpoint Example

This integration is particularly useful for API endpoints:

```php
#[Route('/users', methods: ['POST'])]
public function createUser(ServerRequestInterface $request): ResponseInterface
{
    try {
        // Map and validate incoming JSON to DTO
        $user = $this->mapper->toObject(
            $request->getBody()->getContents(),
            ValidatedUser::class
        );
        
        // Process the validated user data
        $this->userService->create($user);
        
        return new JsonResponse(['success' => true]);
        
    } catch (\CuyZ\Valinor\Mapper\MappingError $e) {
        // Handle validation errors
        return new JsonResponse([
            'error' => 'Validation failed',
            'details' => $e->getMessage()
        ], 400);
    }
}
```

### Error Handling

Both packages provide detailed error information:

```php
try {
    $user = $mapper->toObject($jsonPayload, ValidatedUser::class);
} catch (\CuyZ\Valinor\Mapper\MappingError $e) {
    // Get detailed validation errors
    $errors = [];
    foreach ($e->node()->messages() as $message) {
        $errors[] = [
            'path' => $message->node()->path(),
            'message' => (string) $message,
        ];
    }
    
    // Log or return structured error response
    return new JsonResponse(['validation_errors' => $errors], 400);
}
```

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
