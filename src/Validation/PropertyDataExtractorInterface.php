<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Schema\Type;

interface PropertyDataExtractorInterface
{
    /**
     * Extract validation rules from a property for a specific JSON schema type.
     *
     * @param PropertyInterface $property The property to extract rules from
     * @param Type $jsonSchemaType The JSON schema type to extract rules for
     * @return array<string, mixed> Array of validation rules
     */
    public function extractValidationRules(PropertyInterface $property, Type $jsonSchemaType): array;
}
