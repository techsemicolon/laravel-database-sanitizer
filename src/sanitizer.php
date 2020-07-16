<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Models to Sanitize
    |--------------------------------------------------------------------------
    |
    | Add all models which you would like to sanitize
    |
    */

    'sanitize_models' => [
        // e.g. User::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Models to Truncate
    |--------------------------------------------------------------------------
    |
    | Add all models which you would like to truncate
    |
    */

    'truncate_models' => [
        // e.g. CustomLog::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Chunk count
    |--------------------------------------------------------------------------
    |
    | The chunk count for sanitizer updates
    |
    */

    'chunk_count' => 1000
];
