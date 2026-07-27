<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Support\Arr;

final class ModuleRegistry
{
    /** @var array<string, ModuleDefinition> */
    private array $modules = [];

    public function __construct(
        private readonly string $modulesPath,
    ) {
        $this->modules = $this->discoverModules();
    }

    /**
     * @return array<string, ModuleDefinition>
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * @return array<string, ModuleDefinition>
     */
    public function enabled(): array
    {
        return array_filter(
            $this->modules,
            static fn (ModuleDefinition $module): bool => $module->enabled
        );
    }

    public function find(string $name): ?ModuleDefinition
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * @return array<string, ModuleDefinition>
     */
    private function discoverModules(): array
    {
        $modules = [];

        foreach (glob($this->modulesPath.'/*/module.php') ?: [] as $manifestPath) {
            $module = $this->createModuleDefinition($manifestPath);

            if ($module === null) {
                continue;
            }

            $modules[$module->name] = $module;
        }

        ksort($modules);

        return $modules;
    }

    private function createModuleDefinition(string $manifestPath): ?ModuleDefinition
    {
        $config = require $manifestPath;

        if (! is_array($config)) {
            return null;
        }

        $modulePath = dirname($manifestPath);
        $name = (string) Arr::get($config, 'name', basename($modulePath));
        $enabled = (bool) Arr::get($config, 'enabled', true);
        $providers = array_values(array_filter(Arr::wrap(Arr::get($config, 'providers', []))));
        $routeFiles = array_values(array_filter(Arr::wrap(Arr::get($config, 'route_files', []))));

        return new ModuleDefinition(
            name: $name,
            path: $modulePath,
            enabled: $enabled,
            providers: $providers,
            routeFiles: $routeFiles,
        );
    }
}
