<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

/**
 * @internal
 */
final class Parser implements ParserInterface
{
    /**
     * @param class-string|\ReflectionClass $class
     */
    public function parse(\ReflectionClass|string $class): ClassParserInterface
    {
        return new ClassParser($class);
    }
}
