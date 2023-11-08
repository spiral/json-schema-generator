<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

enum ReleaseStatus: string
{
    case Released = 'Released';
    case Rumored = 'Rumored';
    case PostProduction = 'Post Production';
    case InProduction = 'In Production';
    case Planned = 'Planned';
    case Canceled = 'Canceled';
}
