<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

final class PropertyVisibility
{
    public static string $static = '';
    public $untyped;
    public string $public = '';
    protected string $protected = '';
    private string $private = '';
}
