<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Validation;

use Spiral\JsonSchemaGenerator\Attribute\AdditionalProperties;
use Spiral\JsonSchemaGenerator\Parser\PropertyInterface;
use Spiral\JsonSchemaGenerator\Schema\Reference;
use Spiral\JsonSchemaGenerator\Schema\Type;

final readonly class AdditionalPropertiesExtractor implements PropertyDataExtractorInterface
{
    public function extractValidationRules(PropertyInterface $property, Type $jsonSchemaType): array
    {
        $validationRules = [];

        // Only process array types for additional properties
        if ($jsonSchemaType !== Type::Array) {
            return $validationRules;
        }

        $additionalProperties = $property->findAttribute(AdditionalProperties::class);
        if (!$additionalProperties instanceof AdditionalProperties) {
            return $validationRules;
        }

        // Override array type to object type for additional properties
        $validationRules['type'] = 'object';

        // Process the additional properties value type
        $additionalPropertiesSchema = $this->processValueType(
            $additionalProperties->valueType,
            $additionalProperties->valueClass,
        );

        $validationRules['additionalProperties'] = $additionalPropertiesSchema;

        // Store class dependencies for later extraction
        if ($additionalProperties->valueType === 'object' && $additionalProperties->valueClass !== null) {
            $validationRules['_additionalPropertiesClass'] = $additionalProperties->valueClass;
        }

        return $validationRules;
    }

    /**
     * Process the value type and return appropriate JSON schema structure.
     *
     * @param class-string|null $valueClass
     */
    private function processValueType(string $valueType, ?string $valueClass): array|bool
    {
        return match ($valueType) {
            'int', 'integer' => ['type' => 'integer'],
            'number', 'float' => ['type' => 'number'],
            'boolean', 'bool' => ['type' => 'boolean'],
            'mixed' => true, // Allow any type
            'object' => $this->processObjectType($valueClass),
            default => ['type' => 'string'], // fallback to string
        };
    }

    /**
     * Process object type with class reference.
     *
     * @param class-string|null $valueClass
     */
    private function processObjectType(?string $valueClass): array
    {
        if ($valueClass === null) {
            return ['type' => 'object'];
        }

        // Create reference to the class definition
        return ['$ref' => (new Reference($valueClass))->jsonSerialize()];
    }
}
