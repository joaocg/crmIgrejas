<?php

declare(strict_types=1);

namespace App\Support\Modules;

final readonly class ModuleDefinition
{
    /**
     * @param  array<int, string>  $providers
     * @param  array<int, string>  $routeFiles
     */
    public function __construct(
        public string $name,
        public string $path,
        public bool $enabled = true,
        public array $providers = [],
        public array $routeFiles = [],
    ) {
    }
}
