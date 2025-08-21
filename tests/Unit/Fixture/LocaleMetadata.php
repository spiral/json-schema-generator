<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final readonly class LocaleMetadata
{
    public function __construct(
        public string $author,
        public string $lastModified,
        public string $reviewStatus,
    ) {}
}
