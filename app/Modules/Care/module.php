<?php

declare(strict_types=1);

return [
    'name' => 'Care',
    'enabled' => true,
    'providers' => [
        App\Modules\Care\Providers\CareModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
