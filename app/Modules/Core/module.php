<?php

declare(strict_types=1);

return [
    'name' => 'Core',
    'enabled' => true,
    'providers' => [
        App\Modules\Core\Providers\CoreModuleServiceProvider::class,
    ],
];
