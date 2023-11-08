<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

use Spiral\JsonSchemaGenerator\Exception\InvalidTypeException;

final class Property implements \JsonSerializable
{
    public readonly PropertyOptions $options;

    /**
     * @param Type|class-string $type
     * @param array<class-string|Type> $options
     */
    public function __construct(
        public readonly Type|string $type,
        array $options = [],
        public readonly string $title = '',
        public readonly string $description = '',
        public readonly bool $required = false,
        public readonly mixed $default = null,
    ) {
        if (\is_string($this->type) && !\class_exists($this->type)) {
            throw new InvalidTypeException('Invalid type definition');
        }

        $this->options = new PropertyOptions($options);
    }

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

        if ($this->type === Type::Union) {
            $property['anyOf'] = $this->options->jsonSerialize();
            return $property;
        }

        if (\is_string($this->type)) {
            // this is nested class
            $property['allOf'][] = ['$ref' => (new Reference($this->type))->jsonSerialize()];
            return $property;
        }

        $property['type'] = $this->type->value;

        if ($this->type === Type::Array) {
            if (\count($this->options) === 1) {
                if (\is_string($this->options[0]->value)) {
                    // reference to class
                    $property['items']['$ref'] = (new Reference($this->options[0]->value))->jsonSerialize();
                    return $property;
                }

                $property['items']['type'] = $this->options[0]->value->value;
            } else {
                $property['items']['anyOf'] = $this->options->jsonSerialize();
            }
        }

        return $property;
    }

    public function getDependencies(): array
    {
        $dependencies = [];
        foreach ($this->options->getOptions() as $option) {
            if (is_string($option->value)) {
                $dependencies[] = $option->value;
            }
        }

        if (is_string($this->type)) {
            $dependencies[] = $this->type;
        }

        return $dependencies;
    }
}
