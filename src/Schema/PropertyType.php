<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

final readonly class PropertyType
{
    /**
     * @param class-string|Type $type
     * @param list<int|string>|null $enum
     * @param list<PropertyType>|null $collectionTypes
     */
    public function __construct(
        public string|Type $type,
        public ?array $enum = null,
        public ?array $collectionTypes = null,
    ) {}
}
