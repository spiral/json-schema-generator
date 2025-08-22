<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

/**
 * @internal
 */
final readonly class Property implements \JsonSerializable
{
    /**
     * @param list<PropertyType> $types
     * @param array<string, mixed> $validationRules
     */
    public function __construct(
        public array $types,
        public string $title = '',
        public string $description = '',
        public bool $required = false,
        public mixed $default = null,
        public ?Format $format = null,
        public array $validationRules = [], // NEW: Validation rules from PHPDoc
    ) {}

    public function jsonSerialize(): array
    {
        $property = [];
        if ($this->title !== '') {
            $property['title'] = $this->title;
        }

        if ($this->description !== '') {
            $property['description'] = $this->description;
        }

        if ($this->default !== null) {
            $property['default'] = $this->default;
        }

        if ($this->format instanceof Format) {
            $property['format'] = $this->format->value;
        }

        // Check if we have an array shape constraint that should override the type
        if (isset($this->validationRules['type']) && $this->validationRules['type'] === 'object') {
            // Array shape or additional properties overrides normal type processing
            $property = \array_merge($property, $this->validationRules);

            // Clean up internal metadata keys
            unset($property['_additionalPropertiesClass']);

            return $property;
        }

        $typesCount = \count($this->types);
        if ($typesCount > 1) {
            foreach ($this->types as $type) {
                $property['oneOf'][] = $this->propertyTypeToDefinition($type);
            }
        } elseif ($typesCount === 1) {
            $property = \array_merge($property, $this->propertyTypeToDefinition($this->types[0]));
        }

        // Apply validation rules from PHPDoc constraints (except type overrides)
        $filteredValidationRules = $this->validationRules;
        if (isset($filteredValidationRules['type'])) {
            unset($filteredValidationRules['type'], $filteredValidationRules['properties'], $filteredValidationRules['required'], $filteredValidationRules['additionalProperties'], $filteredValidationRules['_additionalPropertiesClass']);
        }
        $property = \array_merge($property, $filteredValidationRules);

        return $property;
    }

    public function getDependencies(): array
    {
        $dependencies = [];
        foreach ($this->types as $type) {
            if (\is_string($type->type)) {
                $dependencies[] = $type->type;
            }
            if ($type->type === Type::Array && $type->collectionTypes !== null && $type->collectionTypes !== []) {
                foreach ($type->collectionTypes as $collectionType) {
                    if (\is_string($collectionType->type)) {
                        $dependencies[] = $collectionType->type;
                    }
                }
            }
        }

        // Extract dependencies from additional properties references
        if (isset($this->validationRules['_additionalPropertiesClass'])) {
            $dependencies[] = $this->validationRules['_additionalPropertiesClass'];
        }

        return $dependencies;
    }

    protected function propertyTypeToDefinition(PropertyType $propertyType): array
    {
        $property = [];

        if ($propertyType->type instanceof Type) {
            $property['type'] = $propertyType->type->value;
            if ($propertyType->enum !== null) {
                $property['enum'] = $propertyType->enum;
            }
            if ($propertyType->type === Type::Array && $propertyType->collectionTypes !== null && $propertyType->collectionTypes !== []) {
                $collectionTypeCount = \count($propertyType->collectionTypes);
                if ($collectionTypeCount > 1) {
                    foreach ($propertyType->collectionTypes as $collectionType) {
                        if ($collectionType->type instanceof Type) {
                            $schemaType = ['type' => $collectionType->type->value];
                            if ($collectionType->enum !== null) {
                                $schemaType['enum'] = $collectionType->enum;
                            }
                            $property['items']['anyOf'][] = $schemaType;
                        } else {
                            $property['items']['anyOf'][] = [
                                '$ref' => (new Reference(
                                    $collectionType->type,
                                ))->jsonSerialize(),
                            ];
                        }
                    }
                } elseif ($collectionTypeCount === 1) {
                    $collectionType = $propertyType->collectionTypes[0];
                    if ($collectionType->type instanceof Type) {
                        $property['items'] = ['type' => $collectionType->type->value];
                        if ($collectionType->enum !== null) {
                            $property['items']['enum'] = $collectionType->enum;
                        }
                    } else {
                        $property['items'] = ['$ref' => (new Reference($collectionType->type))->jsonSerialize()];
                    }
                }
            }
        } else {
            $property['$ref'] = (new Reference($propertyType->type))->jsonSerialize();
        }

        return $property;
    }
}
