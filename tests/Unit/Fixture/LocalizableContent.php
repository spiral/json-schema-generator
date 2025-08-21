<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Attribute\Field;

final readonly class LocalizableContent
{
    public function __construct(
        public string $contentId,
        public string $contentType,
        #[AdditionalProperties(valueType: 'string')]
        #[Field(description: "Translations for different locales")]
        public array $translations = [],
        #[AdditionalProperties(valueType: 'object', valueClass: LocaleMetadata::class)]
        #[Field(description: "Locale-specific metadata")]
        public array $localeMetadata = [],
    ) {}
}
