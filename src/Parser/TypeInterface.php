<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

use Spiral\JsonSchemaGenerator\Schema\Type as SchemaType;

interface TypeInterface
{
    /**
     * @return class-string|SchemaType
     */
    public function getName(): string|SchemaType;

    public function isBuiltin(): bool;

    public function allowsNull(): bool;
}
