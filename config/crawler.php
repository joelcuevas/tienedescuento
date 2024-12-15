<?php

return [

    'proxy_url' => env('CRAWLER_PROXY_URL'),

    'domains' => [

        'www.liverpool.com.mx' => [
            'queue' => 'liverpool',
            'proxied' => true,
            'throttle_allow' => 120,
            'throttle_every' => 60,
        ],

        'preciominimo.chascity.com' => [
            'queue' => 'chascity',
            'proxied' => false,
            'throttle_allow' => 30,
            'throttle_every' => 60,
        ],

    ],

];
