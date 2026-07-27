<?php

declare(strict_types=1);

return [
    'name' => 'Groups',
    'enabled' => true,
    'providers' => [
        App\Modules\Groups\Providers\GroupsModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
