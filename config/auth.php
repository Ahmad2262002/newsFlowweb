<?php

return [
    'defaults' => [
        'guard' => 'api',  // Changed to API guard
        'passwords' => 'staff',  // Changed to staff passwords
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'staff',  // Changed to staff provider
        ],

        'api' => [
            'driver' => 'sanctum',
            'provider' => 'staff',
            'hash' => false,
        ],
    ],

    'providers' => [
        'staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class,
        ],
    ],

    'passwords' => [
        'staff' => [
            'provider' => 'staff',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];