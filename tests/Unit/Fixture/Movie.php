<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

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
