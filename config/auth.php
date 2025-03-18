<?php

return [
    'defaults' => [
        'guard' => 'api',  // Changed to API guard
        'passwords' => 'staff',  // Changed to staff passwords
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
<<<<<<< HEAD
            'provider' => 'staff',  // Changed to staff provider
        ],

        'api' => [
            'driver' => 'sanctum',
            'provider' => 'staff',
            'hash' => false,
=======
            'provider' => 'users', // Uses the 'users' provider for web authentication
        ],

        'api' => [
            'driver' => 'sanctum', // Use Sanctum for API authentication
            'provider' => 'staff', // Use the 'staff' provider for API authentication
>>>>>>> 1be931d626dd7cf88e724812dbc324d208ca59ae
        ],
    ],

    'providers' => [
        'staff' => [
            'driver' => 'eloquent',
<<<<<<< HEAD
            'model' => App\Models\Staff::class,
=======
            'model' => App\Models\User::class, // Default User model
        ],

        'staff' => [
            'driver' => 'eloquent',
            'model' => App\Models\Staff::class, // Use the Staff model for API authentication
>>>>>>> 1be931d626dd7cf88e724812dbc324d208ca59ae
        ],
    ],

    'passwords' => [
        'staff' => [
            'provider' => 'staff',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'staff' => [
            'provider' => 'staff', // Use the 'staff' provider for password resets
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];