<?php

namespace App\Crawlers\Liverpool;

class SuburbiaProductCrawler extends LiverpoolProductCrawler
{
    protected static ?string $storeCode = 'suburbia-mx';

    protected static ?string $pattern = '#^https://www\.suburbia\.com\.mx/tienda/pdp/(?:.+/)?(\w+)(?:\?.*)?$#';
}
