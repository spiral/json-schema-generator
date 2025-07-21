<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

final class Property implements \JsonSerializable
{
    /**
     * @param list<PropertyType> $types
     */
    public function __construct(
        public readonly array $types,
        public readonly string $title = '',
        public readonly string $description = '',
        public readonly bool $required = false,
        public readonly mixed $default = null,
        public readonly ?Format $format = null,
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

        $typesCount = \count($this->types);
        if ($typesCount > 1) {
            foreach ($this->types as $type) {
                $property['oneOf'][] = $this->propertyTypeToDefinition($type);
            }
        } elseif ($typesCount === 1) {
            $property = \array_merge($property, $this->propertyTypeToDefinition($this->types[0]));
        }

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
                            $property['items']['anyOf'][] = ['$ref' => (new Reference($collectionType->type))->jsonSerialize()];
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
