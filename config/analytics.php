<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Tracking Enabled
    |--------------------------------------------------------------------------
    |
    | Global switch to enable or disable analytics event tracking.
    |
    */
    'enabled' => env('ANALYTICS_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Duplicate View Window (in seconds)
    |--------------------------------------------------------------------------
    |
    | Duration within which repeated views of the same page/entity by the
    | same visitor will be treated as duplicate and ignored.
    | Default is 300 seconds (5 minutes).
    |
    */
    'duplicate_window' => (int) env('ANALYTICS_DUPLICATE_WINDOW', 12*60*60), 
];
