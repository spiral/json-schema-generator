<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Parser;

interface ParserInterface
{
    /**
     * @param class-string|\ReflectionClass $class
     */
    public function parse(\ReflectionClass|string $class): ClassParserInterface;
}
