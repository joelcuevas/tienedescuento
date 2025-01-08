<?php

namespace App\Crawlers\Liverpool;

class SuburbiaCategoryCrawler extends LiverpoolCategoryCrawler
{
    protected static ?string $storeCode = 'suburbia-mx';

    protected static ?string $pattern = '#^https://www\.suburbia\.com\.mx/tienda/[^/]+/[^/]+(/page-\d+)?$#';
}
