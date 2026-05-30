<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Shared secret required on all write endpoints, sent as the `X-Api-Key`
    | header. In production this should be rotated and per-consumer.
    |
    */
    'api_key' => env('HOLD_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Hold TTL
    |--------------------------------------------------------------------------
    |
    | How long a freshly created hold stays active before the scheduler marks
    | it as expired. 15 minutes per the assignment.
    |
    */
    'ttl_minutes' => (int) env('HOLD_TTL_MINUTES', 15),
];