<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from the following domains / hosts will receive stateful API
    | authentication cookies. Typically, these should include your local
    | and production domains which access your API via a frontend SPA.
    |
    */

    'stateful' => explode(',', env(
        'BOUNCER_STATEFUL_DOMAINS',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,'.parse_url(env('APP_URL'), PHP_URL_HOST)
    )),

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes until an issued token will be
    | considered expired. If this value is null, personal game tokens do
    | not expire. This won't tweak the lifetime of first-party sessions.
    |
    */

    'expiration' => [
        'auth' => 1,
        'session' => 30,
        'session_day' => 1440
    ],

];
