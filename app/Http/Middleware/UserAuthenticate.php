<?php

namespace App\Http\Middleware;

use App\Models\Enum\TipeUser;
use Closure;
use Illuminate\Auth\AuthenticationException;

class UserAuthenticate
{
    public function handle($request, Closure $next)
    {
        if (!auth()->user() || auth()->user()->tipe_user !== TipeUser::USER) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
