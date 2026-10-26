<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hotspot Gateway Driver
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "simulated" (mock/local testing), "radius" (future),
    | "omada_sdn" (future TP-Link Omada), "mikrotik" (future RouterOS API).
    |
    */
    'driver' => env('GATEWAY_DRIVER', 'simulated'),

    /*
    |--------------------------------------------------------------------------
    | Gateway Mock Mode
    |--------------------------------------------------------------------------
    |
    | When true, the gateway simulates all hardware authorization, client leases,
    | and bandwidth tracking locally without attempting physical network connections.
    |
    */
    'mock_mode' => env('GATEWAY_MOCK_MODE', true),

    /*
    |--------------------------------------------------------------------------
    | Simulated Failure Testing
    |--------------------------------------------------------------------------
    |
    | Set to true to test application resilience when the gateway is unreachable.
    |
    */
    'simulate_failure' => env('GATEWAY_SIMULATE_FAILURE', false),

    /*
    |--------------------------------------------------------------------------
    | Simulated Network IP Range
    |--------------------------------------------------------------------------
    */
    'ip_subnet' => env('GATEWAY_SUBNET', '192.168.0.'),

    /*
    |--------------------------------------------------------------------------
    | Gateway Disclaimer
    |--------------------------------------------------------------------------
    */
    'disclaimer' => 'Simulated HotSpot Gateway active for local testing. Does not provide physical internet routing.',
];
