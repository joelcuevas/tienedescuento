<?php

return [

    'proxy_url' => env('CRAWLER_PROXY_URL'),

    'domains' => [

        'www.liverpool.com.mx' => [
            'queue' => 'liverpool',
            'proxied' => true,
            'allow' => 50,
            'every' => 1,
        ],

        'www.suburbia.com.mx' => [
            'queue' => 'suburbia',
            'proxied' => true,
            'allow' => 25,
            'every' => 1,
        ],

        'www.elpalaciodehierro.com' => [
            'queue' => 'palacio',
            'proxied' => false,
            'allow' => 50,
            'every' => 1,
        ],

        'www.costco.com.mx' => [
            'queue' => 'costco-mx',
            'proxied' => false,
            'allow' => 25,
            'every' => 1,
        ],

        'www.sears.com.mx' => [
            'queue' => 'sears',
            'proxied' => false,
            'allow' => 25,
            'every' => 1,
        ],

        'preciominimo.chascity.com' => [
            'queue' => 'chascity',
            'proxied' => false,
            'allow' => 15,
            'every' => 1,
        ],

    ],

];
