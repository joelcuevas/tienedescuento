<?php

namespace App\Crawlers\Costco;

class CostcoMxCategoryAlias
{
    protected static array $patterns = [
        '#^https://www\.costco\.com\.mx/(?:[\w-]+/)*c/([\w.-]+)(?:\?page=(\d+))?$#',
    ];

    public static function matchesPattern($href): ?string
    {
        foreach (static::$patterns as $pattern) {
            if (preg_match($pattern, $href, $matches)) {
                $jsonUrl = 'https://www.costco.com.mx/rest/v2/mexico/products/search?category=%s&currentPage=0&pageSize=100&lang=es_MX&curr=MXN&fields=FULL';

                return sprintf($jsonUrl, $matches[1]);
            }
        }

        return null;
    }
}
