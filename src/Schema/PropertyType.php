<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

final class PropertyType
{
    /**
     * @param class-string|Type $type
     * @param list<int|string>|null $enum
     * @param list<PropertyType>|null $collectionTypes
     */
    public function __construct(
        public readonly string|Type $type,
        public readonly ?array $enum = null,
        public readonly ?array $collectionTypes = null,
    ) {}
}
