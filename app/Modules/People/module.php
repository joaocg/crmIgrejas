<?php

declare(strict_types=1);
use App\Modules\People\Providers\PeopleModuleServiceProvider;

return [
    'name' => 'People',
    'enabled' => true,
    'providers' => [
        PeopleModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
