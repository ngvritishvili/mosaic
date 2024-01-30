<?php

namespace App\Enums;

use App\Traits\EnumListTrait;

enum ResolutionPx: int
{
    use EnumListTrait;

    case RHDh = 13;
    case RHDw = 21;
    case R2Kh = 20;
    case R2Kw = 33;
    case R4Kh = 42;
    case R4Kw = 62;
    case R8Kh = 85;
    case R8Kw = 126;
    case R12Kh = 84;
    case R12Kw = 122;
}
