<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

final class Reference implements \JsonSerializable
{
    /**
     * @param class-string $className
     */
    public function __construct(
        private readonly string $className,
    ) {
    }

    public function jsonSerialize(): string
    {
        // basename of the class
        $basename = \substr($this->className, (int) \strrpos($this->className, '\\') + 1);

        return '#/definitions/' . $basename;
    }
}
