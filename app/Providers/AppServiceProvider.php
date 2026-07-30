<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Family;
use App\Models\Person;
use App\Policies\FamilyPolicy;
use App\Policies\PersonPolicy;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Person::class, PersonPolicy::class);
        Gate::policy(Family::class, FamilyPolicy::class);
    }
}
