<?php

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
