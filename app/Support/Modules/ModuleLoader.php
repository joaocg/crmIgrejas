<?php

declare(strict_types=1);

namespace App\Support\Modules;

use Illuminate\Contracts\Foundation\Application;
use RuntimeException;

final class ModuleLoader
{
    public function __construct(
        private readonly Application $app,
        private readonly ModuleRegistry $registry,
    ) {
    }

    public function registerProviders(): void
    {
        foreach ($this->registry->enabled() as $module) {
            foreach ($module->providers as $providerClass) {
                if (! class_exists($providerClass)) {
                    throw new RuntimeException(sprintf(
                        'Module [%s] references missing provider [%s].',
                        $module->name,
                        $providerClass,
                    ));
                }

                $this->app->register($providerClass);
            }
        }
    }

    public function loadRoutes(): void
    {
        foreach ($this->registry->enabled() as $module) {
            foreach ($module->routeFiles as $routeFile) {
                if (is_file($routeFile)) {
                    require $routeFile;
                }
            }
        }
    }

    public function hasOverride(string $moduleName, string $churchSlug): bool
    {
        return $this->overrideFiles($moduleName, $churchSlug) !== [];
    }

    public function loadOverrides(string $moduleName, string $churchSlug): void
    {
        foreach ($this->overrideFiles($moduleName, $churchSlug) as $overrideFile) {
            require $overrideFile;
        }
    }

    /**
     * @return array<int, string>
     */
    private function overrideFiles(string $moduleName, string $churchSlug): array
    {
        $module = $this->registry->find($moduleName);

        if ($module === null) {
            return [];
        }

        $overrideDirectory = $module->path.'/Churches/'.$churchSlug;

        if (! is_dir($overrideDirectory)) {
            return [];
        }

        $files = glob($overrideDirectory.'/*.php') ?: [];
        sort($files);

        return $files;
    }
}
