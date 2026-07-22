<?php

namespace App\Http\Middleware;

use Illuminate\Http\Exceptions\PostTooLargeException;

/**
 * Override Laravel's built-in ValidatePostSize.
 * Allows up to 500MB total POST to support bulk gallery uploads (up to 200 photos).
 */
class ValidatePostSize
{
    public function handle($request, \Closure $next)
    {
        $maxSize = $this->getPostMaxSize();

        if ($maxSize > 0 && $request->server('CONTENT_LENGTH') > $maxSize) {
            throw new PostTooLargeException;
        }

        return $next($request);
    }

    /**
     * 500 MB — covers 200 photos at ~2.5MB each.
     */
    protected function getPostMaxSize(): int
    {
        return 500 * 1024 * 1024; // 500 MB
    }
}
