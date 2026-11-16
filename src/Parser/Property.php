<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

/**
 * @internal
 */
final readonly class Property implements PropertyInterface
{
    public function __construct(
        private \ReflectionProperty $property,
        private Type $type,
        private bool $hasDefaultValue,
        private mixed $defaultValue = null,
    ) {}

    /**
     * @return non-empty-string
     */
    #[\Override]
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
    #[\Override]
    public function findAttribute(string $name): ?object
    {
        $name = $this->property->getAttributes($name);
        if ($name !== []) {
            return $name[0]->newInstance();
        }

        return null;
    }

    #[\Override]
    public function hasDefaultValue(): bool
    {
        return $this->hasDefaultValue;
    }

    #[\Override]
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    #[\Override]
    public function getType(): Type
    {
        return $this->type;
    }
}
