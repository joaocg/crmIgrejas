<?php

declare(strict_types=1);

return [
    'name' => 'Users',
    'enabled' => true,
    'providers' => [
        App\Modules\Users\Providers\UsersModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
