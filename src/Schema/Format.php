<?php

declare(strict_types=1);

namespace Spiral\JsonSchemaGenerator\Schema;

/**
 * Based on https://opis.io/json-schema/2.x/formats.html
 */
enum Format: string
{
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'date-time';
    case Duration = 'duration';
    case Regex = 'regex';
    case Email = 'email';
    case IdnEmail = 'idn-email';
    case Hostname = 'hostname';
    case IdnHostname = 'idn-hostname';
    case Ipv4 = 'ipv4';
    case Ipv6 = 'ipv6';
    case JsonPointer = 'json-pointer';
    case RelativeJsonPointer = 'relative-json-pointer';
    case Uri = 'uri';
    case UriReference = 'uri-reference';
    case UriTemplate = 'uri-template';
    case Iri = 'iri';
    case IriReference = 'iri-reference';
    case Uuid = 'uuid';
}
