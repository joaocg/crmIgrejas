<?php

declare(strict_types=1);

namespace App\Modules\Communications\Actions;

use App\Models\ModuleDefinition;
use App\Models\ModuleSetting;

final class SaveWhatsAppIntegrationAction
{
    public function execute(int $tenantId, array $data): array
    {
        $module = ModuleDefinition::query()->firstOrCreate(
            ['slug' => 'communications'],
            [
                'name' => 'Communications',
                'is_core' => false,
                'is_enabled' => true,
            ],
        );

        $payload = [
            'provider' => $data['provider'],
            'enabled' => (bool) ($data['enabled'] ?? true),
            'settings' => $data['settings'] ?? [],
        ];

        ModuleSetting::query()->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'module_definition_id' => $module->id,
                'key' => 'whatsapp_integration',
            ],
            [
                'value' => $payload,
                'type' => 'json',
                'is_secret' => false,
            ],
        );

        return $payload;
    }
}
