<?php

declare(strict_types=1);

namespace App\Modules\Communications\Actions;

use App\Models\ModuleDefinition;
use App\Models\ModuleSetting;

final class GetWhatsAppIntegrationAction
{
    public function execute(int $tenantId): array
    {
        $module = ModuleDefinition::query()->where('slug', 'communications')->first();

        if (! $module) {
            return $this->defaultPayload();
        }

        $setting = ModuleSetting::query()
            ->where('tenant_id', $tenantId)
            ->where('module_definition_id', $module->id)
            ->where('key', 'whatsapp_integration')
            ->first();

        return $setting?->value ?? $this->defaultPayload();
    }

    private function defaultPayload(): array
    {
        return [
            'provider' => null,
            'enabled' => false,
            'settings' => [],
        ];
    }
}
