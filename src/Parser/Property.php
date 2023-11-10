<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

final class Property implements PropertyInterface
{
    public function __construct(
        private readonly \ReflectionProperty $property,
        private readonly TypeInterface $type,
        private readonly bool $hasDefaultValue,
        private readonly mixed $defaultValue = null,
        private readonly array $collectionValueTypes = [],
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->property->getName();
    }

    /**
     * @template T
     *
     * @param class-string<T> $name The class name of the attribute.
     *
     * @return T|null The attribute or {@see null}, if the requested attribute does not exist.
     */
    public function findAttribute(string $name): ?object
    {
        $name = $this->property->getAttributes($name);
        if ($name !== []) {
            return $name[0]->newInstance();
        }

        return null;
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function isCollection(): bool
    {
        $type = $this->type->getName();
        if (!$type instanceof SchemaType) {
            return false;
        }

        return $type->value === SchemaType::Array->value;
    }

    /**
     * @return array<TypeInterface>
     */
    public function getCollectionValueTypes(): array
    {
        return $this->collectionValueTypes;
    }

    public function getType(): TypeInterface
    {
        return $this->type;
    }
}
