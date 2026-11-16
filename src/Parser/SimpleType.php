<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

/**
 * @internal
 */
final readonly class SimpleType implements TypeInterface
{
    /**
     * @var class-string|SchemaType
     */
    private string|SchemaType $name;

    /**
     * @param non-empty-string|class-string $name
     * @param list<int|string>|null $enum
     */
    public function __construct(
        string $name,
        private bool $builtin,
        private ?Type $collectionType = null,
        private ?array $enum = null,
    ) {
        /** @psalm-suppress PropertyTypeCoercion */
        $this->name = $this->builtin ? SchemaType::fromBuiltIn($name) : $name;
    }

    /**
     * @return class-string|SchemaType
     */
    #[\Override]
    public function getName(): string|SchemaType
    {
        return $this->name;
    }

    #[\Override]
    public function isBuiltin(): bool
    {
        return $this->builtin;
    }

    #[\Override]
    public function isEnum(): bool
    {
        return $this->enum !== null;
    }

    /**
     * @return list<int|string>|null
     */
    #[\Override]
    public function getEnumValues(): ?array
    {
        return $this->enum;
    }

    public function isCollection(): bool
    {
        return $this->collectionType !== null;
    }

    public function getCollectionType(): ?Type
    {
        return $this->collectionType;
    }
}
