<?php

declare(strict_types=1);

return [
    'name' => 'Events',
    'enabled' => true,
    'providers' => [
        App\Modules\Events\Providers\EventsModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
