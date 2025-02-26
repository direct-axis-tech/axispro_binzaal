<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS / Text Messaging
    |--------------------------------------------------------------------------
    |
    | This file is for storing the different configurations for sending sms such
    | as reason8, etisalat, routemobile and others. This file provides a sane default
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'provider' => env('SMS_PROVIDER', 'reason8'),


    'providers' => [

        'reason8' => [
            'id' => env('REASON8_ID', 'reson8rest-oa'),
            'endpoint' => env('REASON8_ENDPOINT', 'https://www.reson8.ae/rest-api/v1/message'),
            'token' => env('REASON8_TOKEN', 'ada2dbe85881dd101af6e4854577f56a30c733eb31de07d84eeb3f83d11c6825'),
            'from' => env('REASON8_FROM', 'DIRECTAXIS'),
            'api_key' => env('REASON8_API_KEY'),
        ],

        'route_mobile' => [
            'endpoint' => env('ROUTEMOBILE_ENDPOINT'),
            'username' => env('ROUTEMOBILE_USERNAME'),
            'password' => env('ROUTEMOBILE_PASSWORD'),
            'source' => env('ROUTEMOBILE_SOURCE'),
            'type' => env('ROUTEMOBILE_TYPE', '0'),
            'dlr' => env('ROUTEMOBILE_DLR', '1')
        ],
        
        'etisalat' => [
            'endpoint' => env('ETISALAT_SMS_ENDPOINT', 'https://smartmessaging.etisalat.ae:5676'),
            'username' => env('ETISALAT_SMS_USERNAME'),
            'password' => env('ETISALAT_SMS_PASSWORD'),
            'sender' => env('ETISALAT_SMS_SENDER_ADDRESS') ?: '',
        ]
    ],

];
