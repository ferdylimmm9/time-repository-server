<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\OauthClient;
use App\Models\OauthPersonalAccessClient;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Passport::hashClientSecrets();
        Passport::tokensExpireIn(now()->addDay());
        Passport::refreshTokensExpireIn(now()->addMonth());
        Passport::personalAccessTokensExpireIn(now()->addYear());

        Passport::useClientModel(OauthClient::class);
        Passport::usePersonalAccessClientModel(OauthPersonalAccessClient::class);
    }
}
