<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Attribute\Constraint;

use Spiral\JsonSchemaGenerator\Attribute\Constraint\Pattern;
use Testo\Assert;
use Testo\Test;

#[Test]
final class PatternTest
{
    public function testPatternAttribute(): void
    {
        $pattern = new Pattern('^[a-zA-Z0-9]+$');

        Assert::same($pattern->pattern, '^[a-zA-Z0-9]+$');
    }

    public function testPatternAttributeWithComplexRegex(): void
    {
        $complexPattern = '^(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$';
        $pattern = new Pattern($complexPattern);

        Assert::same($pattern->pattern, $complexPattern);
    }
}
