<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\Field;
use Spiral\JsonSchemaGenerator\Schema\Format;

final readonly class Series
{
    public function __construct(
        #[Field(title: 'Title', description: 'The title of the series')]
        public string $title,
        #[Field(title: 'First Air Year', description: 'The year the series first aired')]
        public int $firstAirYear,
        #[Field(title: 'Description', description: 'The description of the series')]
        public ?string $description = null,
        #[Field(title: 'Creator', description: 'The creator or showrunner of the series')]
        public ?string $creator = null,
        #[Field(title: 'Series Status', description: 'The current status of the series')]
        public ?SeriesStatus $status = null,
        #[Field(title: 'First Air Date', description: 'The original release date of the series', format: Format::Date)]
        public ?string $firstAirDate = null,
        #[Field(title: 'Last Air Date', description: 'The most recent air date of the series', format: Format::Date)]
        public ?string $lastAirDate = null,
        #[Field(title: 'Seasons', description: 'Number of seasons released')]
        public ?int $seasons = null,
    ) {}
}
