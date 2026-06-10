<?php

return [

    'default' => env('BROADCAST_CONNECTION', 'reverb'),

    'connections' => [

        'reverb' => [
            'driver'   => 'reverb',
            'key'      => env('REVERB_APP_KEY', 'dummy-key'),
            'secret'   => env('REVERB_APP_SECRET', 'dummy-secret'),
            'app_id'   => env('REVERB_APP_ID', 'dummy-id'),
            'options'  => [
                'host'   => env('REVERB_HOST', '0.0.0.0'),
                'port'   => env('REVERB_PORT', 8081),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'useTLS' => env('REVERB_SCHEME', 'http') === 'https',
            ],
            'client_options' => [],
        ],

        'log'  => ['driver' => 'log'],
        'null' => ['driver' => 'null'],
    ],
];
