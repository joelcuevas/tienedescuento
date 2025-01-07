<?php

namespace App\Crawlers\Liverpool;

class SuburbiaSitemapCrawler extends LiverpoolSitemapCrawler
{
    protected static ?string $storeCode = 'suburbia-mx';
    
    protected static ?string $pattern = '#^https://www\.suburbia\.com\.mx/sitemap/[^/]+\.xml$#';
}
