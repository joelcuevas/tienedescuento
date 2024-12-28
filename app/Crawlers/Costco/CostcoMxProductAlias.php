<?php

namespace App\Crawlers\Costco;

class CostcoMxProductAlias
{
    protected static array $patterns = [
        '#^https://www\.costco\.com\.mx/(?:[\w-]+/)*p/([\w-]+)$#',
    ];

    public static function matchesPattern($href): ?string
    {
        foreach (static::$patterns as $pattern) {
            if (preg_match($pattern, $href, $matches)) {
                $jsonUrl = 'https://www.costco.com.mx/rest/v2/mexico/products/%s/?fields=FULL&lang=es_MX&curr=MXN';

                return sprintf($jsonUrl, $matches[1]);
            }
        }

        return null;
    }
}
