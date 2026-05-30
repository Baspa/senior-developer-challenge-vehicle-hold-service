<?php

declare(strict_types=1);

namespace App\Enums;

enum HoldStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Released = 'released';
}