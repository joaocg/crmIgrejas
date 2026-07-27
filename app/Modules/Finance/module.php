<?php

declare(strict_types=1);

return [
    'name' => 'Finance',
    'enabled' => true,
    'providers' => [
        App\Modules\Finance\Providers\FinanceModuleServiceProvider::class,
    ],
    'route_files' => [
        __DIR__.'/Routes/api.php',
    ],
];
