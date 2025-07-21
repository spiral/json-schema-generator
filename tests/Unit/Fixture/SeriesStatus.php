<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Unit\Fixture;

enum SeriesStatus: string
{
    case Airing = 'Airing';
    case Ended = 'Ended';
    case Canceled = 'Canceled';
    case Upcoming = 'Upcoming';
    case Hiatus = 'Hiatus';
}
