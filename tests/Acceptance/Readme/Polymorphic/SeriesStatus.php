<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Tests\Acceptance\Readme\Polymorphic;

enum SeriesStatus: string
{
    case Running = 'Running';
    case Ended = 'Ended';
    case Canceled = 'Canceled';
    case OnHiatus = 'On Hiatus';
}
