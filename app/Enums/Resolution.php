<?php

namespace App\Enums;

use App\Traits\EnumListTrait;

enum Resolution: int
{
    use EnumListTrait;

    case RHD = 1;
    case RFHD = 2;
    case R2K = 3;
    case R4K = 4;
    case R8K = 5;
}
