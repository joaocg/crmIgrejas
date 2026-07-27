<?php

declare(strict_types=1);

return [
    'name' => 'Communications',
    'enabled' => true,
    'providers' => [
        App\Modules\Communications\Providers\CommunicationsModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
