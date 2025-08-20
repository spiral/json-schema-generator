<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final readonly class Movie
{
    public function __construct(
        #[Field(title: 'Title', description: 'The title of the movie')]
        public string $title,
        #[Field(title: 'Year', description: 'The year of the movie')]
        public int $year,
        #[Field(title: 'Description', description: 'The description of the movie')]
        public ?string $description = null,
        public ?string $director = null,
        #[Field(title: 'Release Status', description: 'The release status of the movie')]
        public ?ReleaseStatus $releaseStatus = null,
        #[Field(title: 'Release date', description: 'The release date of the movie', format: Format::Date)]
        public ?string $releaseDate = null,
    ) {}
}
