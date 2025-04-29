<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

interface GeneratorInterface
{
    /**
     * Generates JSON schema.
     *
     * @param class-string|\ReflectionClass $class
     */
    public function generate(string|\ReflectionClass $class): Schema;
}
