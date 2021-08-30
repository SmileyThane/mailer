<?php

return [
    /*
    |--------------------------------------------------------------------------
    | List your email providers
    |--------------------------------------------------------------------------
    |
    | Enjoy a life with multimail
    |
    */
    'use_default_mail_facade_in_tests' => true,

    'emails'  => [
        'thane.crios@gmail.com' => [
            'pass'          => env('MAIL_PASSWORD'),
            'username'      => env('MAIL_USERNAME'),
            'from_name'     => 'Max Musterman',
            'reply_to_mail' => 'reply@example.com',
        ],
        'contact@example.net'  => [
          'pass'          => env('MAIL_PASSWORD'),
          'username'      => env('MAIL_USERNAME'),
          'from_name'     => 'Stan Musterman',
          'reply_to_mail' => 'reply@example.com',
        ],
    ],

    'provider' => [
        'default' => [
            'host'      => env('MAIL_HOST'),
            'port'      => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION'),
        ],
    ],

];
