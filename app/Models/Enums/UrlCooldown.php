<?php

namespace App\Models\Enums;

enum UrlCooldown: int
{
    case TOMORROW = 1;

    case LOW_COST_PAGE = 3;

    case MID_COST_PAGE = 7;

    case NOT_MODIFIED = 8;

    case SANITY_CHECK = 15;

    case HIGH_COST_PAGE = 30;

    case MALFORMED_PAGE = 32;

    case NO_RECRAWL = 1000;
}
