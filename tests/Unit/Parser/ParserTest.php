<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Parser;

use Spiral\JsonSchemaGenerator\Exception\GeneratorException;
use Spiral\JsonSchemaGenerator\Parser\ClassParser;
use Spiral\JsonSchemaGenerator\Parser\Parser;
use Spiral\JsonSchemaGenerator\Tests\Unit\Fixture\Movie;
use Testo\Assert;
use Testo\Assert\ExpectException;
use Testo\Test;

#[Test]
final class ParserTest
{
    public function testParse(): void
    {
        Assert::instanceOf((new Parser())->parse(Movie::class), ClassParser::class);
        Assert::instanceOf((new Parser())->parse(new \ReflectionClass(Movie::class)), ClassParser::class);
    }

    #[ExpectException(GeneratorException::class)]
    public function testParseException(): void
    {
        Assert::instanceOf((new Parser())->parse('foo'), ClassParser::class);
    }
}
