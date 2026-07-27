<?php

declare(strict_types=1);

return [
    'name' => 'People',
    'enabled' => true,
    'providers' => [
        App\Modules\People\Providers\PeopleModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
