<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Validation;

use Spiral\JsonSchemaGenerator\Validation\DocBlockParser;
use Testo\Assert;
use Testo\Lifecycle\BeforeTest;
use Testo\Test;

#[Test]
final class DocBlockParserTest
{
    private DocBlockParser $parser;

    public function testParsePositiveInt(): void
    {
        $docComment = '/** @var positive-int */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::contains($constraints, 'positive-int');
    }

    public function testParseNonEmptyString(): void
    {
        $docComment = '/** @var non-empty-string */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::contains($constraints, 'non-empty-string');
    }

    public function testParseIntRange(): void
    {
        $docComment = '/** @var int<0, 100> */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::contains($constraints, ['int-range' => ['0', '100']]);
    }

    public function testParseIntRangeWithMin(): void
    {
        $docComment = '/** @var int<min, 0> */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::contains($constraints, ['int-range' => ['min', '0']]);
    }

    public function testParseArrayShape(): void
    {
        $docComment = '/** @var array{theme: string, notifications: bool} */';
        $constraints = $this->parser->parseDocComment($docComment);

        $expected = ['array-shape' => ['theme' => 'string', 'notifications' => 'bool']];
        Assert::contains($constraints, $expected);
    }

    public function testParseNumericString(): void
    {
        $docComment = '/** @var numeric-string */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::contains($constraints, 'numeric-string');
    }

    public function testParseNonEmptyArray(): void
    {
        $docComment = '/** @var non-empty-array<string> */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::contains($constraints, 'non-empty-array');
    }

    public function testParseMultipleConstraints(): void
    {
        $docComment = '/** @var positive-int */';
        $constraints = $this->parser->parseDocComment($docComment);

        Assert::array($constraints);
        Assert::notBlank($constraints);
    }

    #[BeforeTest]
    protected function setUp(): void
    {
        $this->parser = new DocBlockParser();
    }
}
