<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Schema\Property;

abstract class AbstractDefinition implements \JsonSerializable
{
    /**
     * @var array<non-empty-string, Property>
     */
    protected array $properties = [];

    /**
     * @param non-empty-string $name
     */
    public function addProperty(string $name, Property $property): self
    {
        $this->properties[$name] = $property;

        return $this;
    }

    protected function renderProperties(array $schema): array
    {
        if ($this->properties === []) {
            return $schema;
        }

        $schema['properties'] = [];

        // Building properties
        $required = [];
        foreach ($this->properties as $name => $property) {
            $schema['properties'][$name] = $property->jsonSerialize();

            if ($property->required) {
                $required[] = $name;
            }
        }

        if ($required !== []) {
            $schema['required'] = $required;
        }

        return $schema;
    }
}
