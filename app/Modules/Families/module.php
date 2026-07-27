<?php

declare(strict_types=1);

return [
    'name' => 'Families',
    'enabled' => true,
    'providers' => [
        App\Modules\Families\Providers\FamiliesModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
