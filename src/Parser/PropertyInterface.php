<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

interface PropertyInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;

    /**
     * @template T
     *
     * @param class-string<T> $name The class name of the attribute.
     *
     * @return T|null The attribute or {@see null}, if the requested attribute does not exist.
     */
    public function findAttribute(string $name): ?object;

    public function hasDefaultValue(): bool;

    public function getDefaultValue(): mixed;

    public function isCollection(): bool;

    /**
     * @return array<TypeInterface>
     */
    public function getCollectionValueTypes(): array;

    public function getType(): TypeInterface;
}
