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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],

    'sms_providers' => [
            'keylines' => [
                'base_url'  => 'https://sms.digitalsms.net/api/v3/sendsms',//'http://sms.keylines.net/V2/http-api.php',
                'senderid'    => env('SMS_SENDER_ID'),
                'authkey'   => env('SMS_AUTH_KEY')
            ]
    ],

    // Matches the DLT-approved OTP request used by the mobile application.
    'digital_sms' => [
        'url' => env('DIGITAL_SMS_URL', 'https://sms.digitalsms.net/api/v3/sendsms'),
        'token' => env('DIGITAL_SMS_TOKEN', '198|td0aaBizzgjMwRgKcQfn8VTYguWUXCs2fo6hSsYIabc9f13f'),
        'entity_id' => env('DIGITAL_SMS_ENTITY_ID', '1201159375531154788'),
        'sender_id' => env('DIGITAL_SMS_SENDER_ID', 'KEYLNS'),
        'template_id' => env('DIGITAL_SMS_TEMPLATE_ID', '1307162333099680070'),
    ],

];
