<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Override PHP runtime limits for gallery/file upload routes.
 * Ensures 200 photos can be uploaded in a single batch.
 */
class IncreasePostSize
{
    public function handle(Request $request, Closure $next)
    {
        @ini_set('post_max_size',       '500M');
        @ini_set('upload_max_filesize', '100M');
        @ini_set('max_file_uploads',    '200');
        @ini_set('max_input_vars',      '5000');
        @ini_set('max_execution_time',  '300'); // 5 minutes for large uploads
        @ini_set('memory_limit',        '512M');

        return $next($request);
    }
}
