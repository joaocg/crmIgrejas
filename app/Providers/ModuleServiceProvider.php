<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\Modules\ModuleLoader;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Support\ServiceProvider;

final class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ModuleRegistry::class, static fn (): ModuleRegistry => new ModuleRegistry(
            modulesPath: base_path('app/Modules'),
        ));

        $this->app->singleton(ModuleLoader::class, static fn ($app): ModuleLoader => new ModuleLoader(
            app: $app,
            registry: $app->make(ModuleRegistry::class),
        ));

        $this->app->make(ModuleLoader::class)->registerProviders();
    }

    public function boot(ModuleLoader $loader): void
    {
        $loader->loadRoutes();
    }
}
