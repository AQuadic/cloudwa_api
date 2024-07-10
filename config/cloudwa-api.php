<?php

// config for AQuadic/Cloudwa
return [

    /*
    |--------------------------------------------------------------------------
    | API TOKEN TO AUTHENTICATE WITH AQ SERVER. (YOU CAN CREATE ONE FROM PROFILE).
    */
    'api_token' => env('CLOUDWA_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | SESSION UUID (THIS IS UNIQUE FOR EACH SESSION).
    */
    'uuids' => [
        'default' => env('CLOUDWA_DEFAULT_SESSION_UUID', ''),
        // add other session here.
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Number
    */
    'otp' => [
        'shared' => env('CLOUDWA_SHARED_OTP_NUMBERS', true),
        'private' => env('CLOUDWA_OTP_NUMBER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | TEAM ID (THIS IS UNIQUE FOR EACH ACCOUNT).
    */
    'team_id' => env('CLOUDWA_TEAM_ID', ''),
];
