<?php

namespace App\Http\Middleware;

use App\Models\Enum\TipeUser;
use Closure;
use Illuminate\Auth\AuthenticationException;

class AdminAuthenticate
{

    public function handle($request, Closure $next)
    {
        if (!auth()->user() || auth()->user()->tipe_user !== TipeUser::ADMIN) {
            throw new AuthenticationException('Unauthenticated.');
        }

        return $next($request);
    }
}
