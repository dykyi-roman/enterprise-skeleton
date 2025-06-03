<?php

declare(strict_types=1);

namespace Shared\DomainModel\Enum;

enum GeneralErrorCode: int
{
    case UNEXPECTED_ERROR = 100;
    case RATE_LIMIT_EXCEEDED = 101;
}
