<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'client_id' => '889213402583913', 
        'client_secret' => 'daef14be44c76a09ad850a8549ec7036',
        'redirect' => 'https://www.nexttripholiday.com/auth/facebook/callback',
        
    ],

    'google' => [
        'client_id' => '711114041784-h1rjpgfen29p5taosdlv0ha0rhsg3a2l.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect' => 'https://www.nexttripholiday.com/google/callback',
    ],
];
