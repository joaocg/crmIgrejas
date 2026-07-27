<?php

use App\Providers\AppServiceProvider;
use App\Providers\ModuleServiceProvider;
use App\Providers\TelescopeServiceProvider;
use Laravel\Sanctum\SanctumServiceProvider;

return [
    AppServiceProvider::class,
    ModuleServiceProvider::class,
    TelescopeServiceProvider::class,
    SanctumServiceProvider::class,
];
