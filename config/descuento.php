<?php

return [

    'crawler' => [
        'proxy_url' => env('CRAWLER_PROXY_URL'),

        'domains' => [
            'www-liverpool-com-mx' => [
                'queue' => 'liverpool',
                'throttle' => [
                    'allow' => 60,
                    'every' => 60,
                ],
            ],
        ],
    ],

];
