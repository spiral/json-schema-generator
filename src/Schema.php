<?php

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Schema\Definition;

final class Schema extends AbstractDefinition
{
    private array $definitions = [];

    public function addDefinition(string $name, Definition $definition): self
    {
        $this->definitions[$name] = $definition;
        return $this;
    }

    public function jsonSerialize(): array
    {
        $schema = $this->renderProperties([]);

        if ($this->definitions !== []) {
            $schema['definitions'] = [];

            foreach ($this->definitions as $name => $definition) {
                $schema['definitions'][$name] = $definition->jsonSerialize();
            }
        }

        return $schema;
    }
}
