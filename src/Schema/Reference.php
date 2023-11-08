<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

/**
 * @internal
 */
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
        if (!\strrpos($this->className, '\\')) {
            return '#/definitions/' . $this->className;
        }

        // basename of the class
        $basename = \substr($this->className, (int) \strrpos($this->className, '\\') + 1);

        return '#/definitions/' . $basename;
    }
}
