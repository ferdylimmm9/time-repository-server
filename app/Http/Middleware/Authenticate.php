<?php

namespace App\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    protected function unauthenticated($request, array $guards): void
    {
        if ($request->expectsJson()) {
            throw new AuthenticationException('Unauthenticated.', $guards);
        }

        abort(401);
    }
}
