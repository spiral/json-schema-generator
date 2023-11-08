<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

/**
 * @internal
 * @implements \ArrayAccess<int, PropertyOption>
 */
final class PropertyOptions implements \Countable, \ArrayAccess, \JsonSerializable
{
    /**
     * @var array<PropertyOption>
     */
    private array $options = [];

    /**
     * @param array<class-string|Type> $options
     */
    public function __construct(array $options = [])
    {
        foreach ($options as $option) {
            $this->options[] = new PropertyOption($option);
        }
    }

    /**
     * @return array<PropertyOption>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function count(): int
    {
        return \count($this->options);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->options[$offset]);
    }

    public function offsetGet(mixed $offset): PropertyOption
    {
        return $this->options[$offset];
    }

    /**
     * @param int $offset
     * @param PropertyOption|class-string|Type $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->options[$offset] = $value instanceof PropertyOption ? $value : new PropertyOption($value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->options[$offset]);
    }

    public function jsonSerialize(): array
    {
        $types = [];
        foreach ($this->options as $option) {
            if (\is_string($option->value)) {
                // reference to class
                $types[] = ['$ref' => (new Reference($option->value))->jsonSerialize()];
                continue;
            }

            $types[] = ['type' => $option->value->value];
        }
        return $types;
    }
}
