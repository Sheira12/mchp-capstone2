<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    */
    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    */
    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'                   => false,
        ],

        /*
        |----------------------------------------------------------------------
        | Supabase Storage (S3-compatible)
        |----------------------------------------------------------------------
        | Supabase exposes an S3-compatible API at:
        |   https://{project-ref}.supabase.co/storage/v1/s3
        |
        | Set these environment variables in Render → Environment:
        |   SUPABASE_URL          = https://{ref}.supabase.co
        |   SUPABASE_STORAGE_KEY  = service_role JWT (from Project Settings → API)
        |   SUPABASE_BUCKET       = mhcp-media  (create this bucket in Supabase dashboard)
        |   SUPABASE_REGION       = ap-southeast-1  (or your project's region)
        |
        | IMPORTANT: use_path_style_endpoint MUST be true for Supabase S3.
        | visibility 'public' means objects are accessible without a signed URL
        | (requires the bucket to be set to Public in Supabase dashboard).
        |----------------------------------------------------------------------
        */
        'supabase' => [
            'driver'                  => 's3',
            'key'                     => env('SUPABASE_ACCESS_KEY_ID', env('SUPABASE_STORAGE_KEY')),
            'secret'                  => env('SUPABASE_SECRET_ACCESS_KEY', env('SUPABASE_STORAGE_KEY')),
            'region'                  => env('SUPABASE_REGION', 'ap-southeast-1'),
            'bucket'                  => env('SUPABASE_BUCKET', 'mhcp-media'),
            'endpoint'                => env('SUPABASE_URL') . '/storage/v1/s3',
            'use_path_style_endpoint' => true,   // required for Supabase S3
            'url'                     => env('SUPABASE_URL') . '/storage/v1/object/public/' . env('SUPABASE_BUCKET', 'mhcp-media'),
            'visibility'              => 'public',
            'throw'                   => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
