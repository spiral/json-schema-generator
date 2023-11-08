<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

use Spiral\JsonSchemaGenerator\AbstractDefinition;
use Spiral\JsonSchemaGenerator\Exception\DefinitionException;

final class Definition extends AbstractDefinition
{
    /**
     * @param Type|class-string $type
     */
    public function __construct(
        private readonly Type|string $type,
        public readonly array $options = [],
        public readonly string $title = '',
        public readonly string $description = '',
        array $properties = [],
    ) {
        foreach ($properties as $name => $property) {
            if (!$property instanceof Property) {
                throw new DefinitionException(
                    sprintf(
                        'Property "%s" is not an instance of "%s"',
                        // type name or class name
                        is_object($property) ? get_class($property) : gettype($property),
                        Property::class
                    )
                );
            }

            $this->addProperty($name, $property);
        }
    }

    public function jsonSerialize(): array
    {
        $schema = [];
        if ($this->title !== '') {
            $schema['title'] = $this->title;
        }

        if ($this->description !== '') {
            $schema['description'] = $this->description;
        }

        if ($this->type instanceof Type) {
            $schema['type'] = $this->type->value;
        } else {
            $schema = $this->renderType($schema);
        }

        return $this->renderProperties($schema);
    }

    private function renderType(array $schema): array
    {
        if ($this->properties !== []) {
            $schema['type'] = 'object';
            return $this->renderProperties($schema);
        }

        $rf = new \ReflectionClass($this->type);
        if (!$rf->isEnum()) {
            throw new DefinitionException(
                sprintf(
                    'Type `%s` is not an enum, or class with no properties.',
                    $this->type instanceof Type ? $this->type->value : $this->type
                )
            );
        }

        $rf = new \ReflectionEnum($this->type);

        /** @var \ReflectionEnum $rf */
        if (!$rf->isBacked()) {
            throw new DefinitionException(
                sprintf(
                    'Type `%s` is not a backed enum.',
                    $this->type instanceof Type ? $this->type->value : $this->type
                )
            );
        }

        /**
         * @var \ReflectionNamedType $type
         */
        $type = $rf->getBackingType();
        if (!$type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
            throw new DefinitionException(
                \sprintf(
                    'Type `%s` is not a backed enum.',
                    $this->type instanceof Type ? $this->type->value : $this->type
                )
            );
        }

        // mapping to json schema type
        $schema['type'] = match ($type->getName()) {
            'int', 'float' => 'number',
            'string' => 'string',
            'bool' => 'boolean',
            default => throw new DefinitionException(
                \sprintf(
                    'Type `%s` is not a backed enum.',
                    $this->type instanceof Type ? $this->type->value : $this->type
                )
            ),
        };

        // options are scalar values at this point
        $schema['enum'] = $this->options;

        return $schema;
    }
}
