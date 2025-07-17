<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

/**
 * @internal
 */
final class Type implements TypeInterface
{
    /**
     * @var class-string|SchemaType
     */
    private string|SchemaType $name;

    /**
     * @param non-empty-string|class-string $name
     */
    public function __construct(
        string $name,
        private readonly bool $builtin,
        private readonly bool $nullable,
        private readonly ?array $enum = null,
    ) {
        /** @psalm-suppress PropertyTypeCoercion */
        $this->name = $this->builtin ? SchemaType::fromBuiltIn($name) : $name;
    }

    /**
     * @return class-string|SchemaType
     */
    public function getName(): string|SchemaType
    {
        return $this->name;
    }

    public function isBuiltin(): bool
    {
        return $this->builtin;
    }

    public function allowsNull(): bool
    {
        return $this->nullable;
    }

    public function isEnum(): bool
    {
        return $this->enum !== null;
    }

    public function getEnumValues(): ?array
    {
        return $this->enum;
    }
}
