<?php

namespace App\Enums;

enum MembershipStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Frozen = 'frozen';
}
