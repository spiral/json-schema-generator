<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

/**
 * @internal
 */
final readonly class Type
{
    /**
     * @param list<SimpleType> $types
     */
    public function __construct(
        public array $types,
    ) {}

    public function allowsNull(): bool
    {
        return \count(\array_filter($this->types, static fn(SimpleType $type): bool => $type->getName() === SchemaType::Null)) !== 0;
    }
}
