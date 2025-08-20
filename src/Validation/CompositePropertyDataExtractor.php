<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Schema\Type;

final readonly class CompositePropertyDataExtractor implements PropertyDataExtractorInterface
{
    /**
     * @param list<PropertyDataExtractorInterface> $extractors
     */
    public function __construct(
        private array $extractors = [],
    ) {}

    /**
     * Create a default instance with commonly used extractors.
     */
    public static function createDefault(): self
    {
        return new self([
            new ValidationConstraintExtractor(),
            new AttributeConstraintExtractor(),
        ]);
    }

    /**
     * Add an extractor to the composite.
     */
    public function withExtractor(PropertyDataExtractorInterface $extractor): self
    {
        return new self([...$this->extractors, $extractor]);
    }

    public function extractValidationRules(PropertyInterface $property, Type $jsonSchemaType): array
    {
        $allValidationRules = [];

        foreach ($this->extractors as $extractor) {
            $validationRules = $extractor->extractValidationRules($property, $jsonSchemaType);
            $allValidationRules = \array_merge($allValidationRules, $validationRules);
        }

        return $allValidationRules;
    }
}
