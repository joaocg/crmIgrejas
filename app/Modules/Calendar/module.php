<?php

declare(strict_types=1);

return [
    'name' => 'Calendar',
    'enabled' => true,
    'providers' => [
        App\Modules\Calendar\Providers\CalendarModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
