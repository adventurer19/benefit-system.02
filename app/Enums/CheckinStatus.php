<?php

namespace App\Enums;

enum CheckinStatus: string
{
    case Success = 'success';
    case Denied = 'denied';
}
