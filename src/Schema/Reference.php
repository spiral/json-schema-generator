<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

/**
 * @internal
 */
final readonly class Reference implements \JsonSerializable
{
    /**
     * @param class-string $className
     */
    public function __construct(
        private string $className,
    ) {}

    public function jsonSerialize(): string
    {
        $pos = \strrpos($this->className, '\\');

        return '#/definitions/' . ($pos === false ? $this->className : \substr($this->className, $pos + 1));
    }
}
