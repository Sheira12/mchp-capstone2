<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'paymongo' => [
        'public_key'      => env('PAYMONGO_PUBLIC_KEY'),
        'secret_key'      => env('PAYMONGO_SECRET_KEY'),
        'webhook_secret'  => env('PAYMONGO_WEBHOOK_SECRET'),
        // Override the base URL PayMongo redirects back to.
        // Useful in local dev where APP_URL is a LAN address unreachable by PayMongo.
        // Set this in .env to your deployed Render URL when testing locally:
        //   PAYMONGO_RETURN_URL_BASE=https://mchp-capstone2.onrender.com
        // Leave empty on Render (APP_URL is used automatically).
        'return_url_base' => env('PAYMONGO_RETURN_URL_BASE'),
    ],

    'semaphore' => [
        'api_key'     => env('SEMAPHORE_API_KEY'),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'MHCParish'),
    ],

];
