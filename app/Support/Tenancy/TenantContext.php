<?php

declare(strict_types=1);

namespace App\Support\Tenancy;

use Illuminate\Support\Facades\Auth;

final class TenantContext
{
    private ?int $tenantId = null;

    private bool $overridden = false;

    public function id(): ?int
    {
        if ($this->overridden) {
            return $this->tenantId;
        }

        $user = Auth::user();

        if ($user === null || $user->tenant_id === null) {
            return null;
        }

        return (int) $user->tenant_id;
    }

    public function forTenant(?int $tenantId): void
    {
        $this->tenantId = $tenantId;
        $this->overridden = true;
    }

    public function forget(): void
    {
        $this->tenantId = null;
        $this->overridden = false;
    }

    public function runAs(?int $tenantId, callable $callback): mixed
    {
        $previousTenantId = $this->tenantId;
        $previousOverridden = $this->overridden;

        $this->forTenant($tenantId);

        try {
            return $callback();
        } finally {
            $this->tenantId = $previousTenantId;
            $this->overridden = $previousOverridden;
        }
    }
}
