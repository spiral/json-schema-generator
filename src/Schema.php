<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator;

use Spiral\JsonSchemaGenerator\Schema\Definition;

/**
 * @internal
 */
final class Schema extends AbstractDefinition
{
    /** @var array<string, Definition> */
    private array $definitions = [];

    public function addDefinition(string $name, Definition $definition): self
    {
        $this->definitions[$name] = $definition;
        return $this;
    }

    #[\Override]
    public function jsonSerialize(): array
    {
        $schema = $this->renderProperties(['type' => 'object']);

        if ($this->definitions !== []) {
            $schema['definitions'] = [];

            foreach ($this->definitions as $name => $definition) {
                $schema['definitions'][$name] = $definition->jsonSerialize();
            }
        }

        return $schema;
    }
}
