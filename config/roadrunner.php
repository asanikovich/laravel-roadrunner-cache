<?php

/*
|--------------------------------------------------------------------------
| RoadRunner Options
|--------------------------------------------------------------------------
|
| While using RoadRunner, you may define additional options to set up RPC (host and port),
| HTTP middlewares and Cache key.
*/

return [
    'rpc' => [
        'host' => env('ROADRUNNER_RPC_HOST', '127.0.0.1'),
        'port' => env('ROADRUNNER_RPC_PORT', 6001),
    ],
];
