<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZKTeco MB460 Device Configuration
    |--------------------------------------------------------------------------
    */

    'ip'        => env('ZK_TECO_IP', '192.168.1.100'),
    'port'      => (int) env('ZK_TECO_PORT', 4370),
    'password'  => (int) env('ZK_TECO_PASSWORD', 1324),
    'device_id' => (int) env('ZK_TECO_DEVICE_ID', 1),
    'timeout'   => (int) env('ZK_TECO_TIMEOUT', 30),
    'mock_mode' => (bool) env('ZK_TECO_MOCK_MODE', false),
];
