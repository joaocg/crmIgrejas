<?php

declare(strict_types=1);

return [
    'name' => 'Kiosk',
    'enabled' => true,
    'providers' => [
        App\Modules\Kiosk\Providers\KioskModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
