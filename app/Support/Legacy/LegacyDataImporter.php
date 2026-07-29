<?php

namespace App\Support\Legacy;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class LegacyDataImporter
{
    public function import(string $connectionName = 'legacy', int $batchSize = 100): array
    {
        $this->ensureLegacyConnectionExists($connectionName);

        $tenantId = $this->ensureDefaultTenant();

        return app(\App\Support\Tenancy\TenantContext::class)->runAs($tenantId, function () use ($connectionName, $batchSize, $tenantId): array {
            $adminRoleId = $this->ensureAdminRole($tenantId);
            $this->ensureAdminUser($tenantId, $adminRoleId);

            $counts = [
                'families' => 0,
                'persons' => 0,
            ];

            $legacy = DB::connection($connectionName);

            if (Schema::connection($connectionName)->hasTable('family_fam')) {
                $legacy->table('family_fam')
                    ->orderBy('fam_ID')
                    ->chunk($batchSize, function ($rows) use ($tenantId, &$counts) {
                        foreach ($rows as $row) {
                            $this->importFamily($tenantId, $row);
                            $counts['families']++;
                        }
                    });
            }

            if (Schema::connection($connectionName)->hasTable('person_per')) {
                $legacy->table('person_per')
                    ->orderBy('per_ID')
                    ->chunk($batchSize, function ($rows) use ($tenantId, $connectionName, &$counts) {
                        foreach ($rows as $row) {
                            $this->importPerson($tenantId, $connectionName, $row);
                            $counts['persons']++;
                        }
                    });
            }

            return $counts;
        });
    }

    protected function ensureLegacyConnectionExists(string $connectionName): void
    {
        if (! array_key_exists($connectionName, config('database.connections', []))) {
            throw new RuntimeException("Legacy database connection [{$connectionName}] is not configured.");
        }
    }

    protected function ensureDefaultTenant(): int
    {
        $now = now();

        DB::table('tenants')->updateOrInsert(
            ['slug' => 'default'],
            [
                'name' => 'Default Church',
                'locale' => 'pt_BR',
                'timezone' => 'America/Fortaleza',
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return (int) DB::table('tenants')->where('slug', 'default')->value('id');
    }

    protected function ensureAdminRole(int $tenantId): int
    {
        $now = now();

        DB::table('roles')->updateOrInsert(
            ['tenant_id' => $tenantId, 'slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'System administrator',
                'permissions' => json_encode(['*' => true]),
                'is_system' => true,
                'active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return (int) DB::table('roles')
            ->where('tenant_id', $tenantId)
            ->where('slug', 'admin')
            ->value('id');
    }

    protected function ensureAdminUser(int $tenantId, int $roleId): void
    {
        $now = now();

        foreach (['admin@localhost', 'admin@church.local'] as $email) {
            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'tenant_id' => $tenantId,
                    'role_id' => $roleId,
                    'name' => 'Admin',
                    'locale' => 'pt_BR',
                    'active' => true,
                    'password' => Hash::make('password'),
                    'email_verified_at' => $now,
                    'remember_token' => null,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }

    protected function importFamily(int $tenantId, object $legacyFamily): int
    {
        $addressId = $this->upsertAddress($tenantId, [
            'line1' => $this->legacyValue($legacyFamily, 'fam_Address1'),
            'line2' => $this->legacyValue($legacyFamily, 'fam_Address2'),
            'city' => $this->legacyValue($legacyFamily, 'fam_City'),
            'state' => $this->legacyValue($legacyFamily, 'fam_State'),
            'postal_code' => $this->legacyValue($legacyFamily, 'fam_Zip'),
            'country' => $this->legacyValue($legacyFamily, 'fam_Country'),
            'latitude' => $this->legacyValue($legacyFamily, 'fam_Latitude'),
            'longitude' => $this->legacyValue($legacyFamily, 'fam_Longitude'),
        ]);

        $now = now();

        DB::table('families')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'name' => $legacyFamily->fam_Name,
            ],
            [
                'address_id' => $addressId,
                'wedding_date' => $this->legacyValue($legacyFamily, 'fam_WeddingDate'),
                'email' => $this->legacyValue($legacyFamily, 'fam_Email'),
                'home_phone' => $this->legacyValue($legacyFamily, 'fam_HomePhone'),
                'work_phone' => $this->legacyValue($legacyFamily, 'fam_WorkPhone'),
                'mobile_phone' => $this->legacyValue($legacyFamily, 'fam_CellPhone'),
                'envelope_number' => $this->legacyValue($legacyFamily, 'fam_Envelope'),
                'newsletter_enabled' => (bool) ($this->legacyValue($legacyFamily, 'fam_SendNewsLetter', true)),
                'canvass_allowed' => (bool) ($this->legacyValue($legacyFamily, 'fam_OkToCanvass', true)),
                'deactivated_at' => $this->legacyValue($legacyFamily, 'fam_DateDeactivated'),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $familyId = (int) DB::table('families')
            ->where('tenant_id', $tenantId)
            ->where('name', $legacyFamily->fam_Name)
            ->value('id');

        $this->syncFamilyContacts($tenantId, $familyId, $legacyFamily);

        return $familyId;
    }

    protected function importPerson(int $tenantId, string $connectionName, object $legacyPerson): int
    {
        $familyName = null;
        $familyId = null;
        $addressId = null;

        if ($this->legacyValue($legacyPerson, 'per_fam_ID') !== null && Schema::connection($connectionName)->hasTable('family_fam')) {
            $family = DB::connection($connectionName)->table('family_fam')
                ->where('fam_ID', $this->legacyValue($legacyPerson, 'per_fam_ID'))
                ->first();

            if ($family) {
                $familyId = $this->importFamily($tenantId, $family);
                $familyName = $family->fam_Name;
                $addressId = DB::table('families')->whereKey($familyId)->value('address_id');
            }
        }

        if ($addressId === null) {
            $addressId = $this->upsertAddress($tenantId, [
                'line1' => null,
                'line2' => null,
                'city' => null,
                'state' => null,
                'postal_code' => null,
                'country' => null,
                'latitude' => null,
                'longitude' => null,
            ]);
        }

        $now = now();

        DB::table('persons')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'first_name' => $this->legacyValue($legacyPerson, 'per_FirstName'),
                'last_name' => $this->legacyValue($legacyPerson, 'per_LastName'),
                'family_id' => $familyId,
            ],
            [
                'address_id' => $addressId,
                'title' => $this->legacyValue($legacyPerson, 'per_Title'),
                'middle_name' => $this->legacyValue($legacyPerson, 'per_MiddleName'),
                'suffix' => $this->legacyValue($legacyPerson, 'per_Suffix'),
                'birth_date' => $this->combineBirthDate($legacyPerson),
                'membership_date' => $this->legacyValue($legacyPerson, 'per_MembershipDate'),
                'gender' => $this->legacyValue($legacyPerson, 'per_Gender') !== null ? (int) $this->legacyValue($legacyPerson, 'per_Gender') : null,
                'envelope_number' => $this->legacyValue($legacyPerson, 'per_Envelope'),
                'newsletter_enabled' => (bool) ($this->legacyValue($legacyPerson, 'per_SendNewsLetter', true)),
                'deactivated_at' => $this->legacyValue($legacyPerson, 'per_DateDeactivated'),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $personId = (int) DB::table('persons')
            ->where('tenant_id', $tenantId)
            ->where('first_name', $this->legacyValue($legacyPerson, 'per_FirstName'))
            ->where('last_name', $this->legacyValue($legacyPerson, 'per_LastName'))
            ->where('family_id', $familyId)
            ->value('id');

        $this->syncPersonContacts($tenantId, $personId, $legacyPerson);

        return $personId;
    }

    protected function syncFamilyContacts(int $tenantId, int $familyId, object $legacyFamily): void
    {
        $this->syncContact($tenantId, null, $familyId, 'email', 'Email', $this->legacyValue($legacyFamily, 'fam_Email'), true);
        $this->syncContact($tenantId, null, $familyId, 'home_phone', 'Home phone', $this->legacyValue($legacyFamily, 'fam_HomePhone'));
        $this->syncContact($tenantId, null, $familyId, 'work_phone', 'Work phone', $this->legacyValue($legacyFamily, 'fam_WorkPhone'));
        $this->syncContact($tenantId, null, $familyId, 'mobile_phone', 'Mobile phone', $this->legacyValue($legacyFamily, 'fam_CellPhone'));
    }

    protected function syncPersonContacts(int $tenantId, int $personId, object $legacyPerson): void
    {
        $this->syncContact($tenantId, $personId, null, 'email', 'Email', $this->legacyValue($legacyPerson, 'per_Email'), true);
        $this->syncContact($tenantId, $personId, null, 'mobile_phone', 'Mobile phone', $this->legacyValue($legacyPerson, 'per_CellPhone'));
    }

    protected function syncContact(
        int $tenantId,
        ?int $personId,
        ?int $familyId,
        string $type,
        ?string $label,
        mixed $value,
        bool $isPrimary = false
    ): void {
        if ($value === null || $value === '') {
            return;
        }

        $now = now();

        DB::table('contacts')->updateOrInsert(
            [
                'tenant_id' => $tenantId,
                'person_id' => $personId,
                'family_id' => $familyId,
                'type' => $type,
                'value' => $value,
            ],
            [
                'label' => $label,
                'is_primary' => $isPrimary,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    protected function upsertAddress(int $tenantId, array $attributes): ?int
    {
        if (! array_filter($attributes, fn ($value) => $value !== null && $value !== '')) {
            return null;
        }

        $now = now();
        $lookup = array_merge(['tenant_id' => $tenantId], $attributes);
        unset($lookup['latitude'], $lookup['longitude']);

        DB::table('addresses')->updateOrInsert(
            $lookup,
            array_merge($attributes, [
                'tenant_id' => $tenantId,
                'updated_at' => $now,
                'created_at' => $now,
            ])
        );

        return (int) DB::table('addresses')
            ->where('tenant_id', $tenantId)
            ->where('line1', $attributes['line1'])
            ->where('line2', $attributes['line2'])
            ->where('city', $attributes['city'])
            ->where('state', $attributes['state'])
            ->where('postal_code', $attributes['postal_code'])
            ->where('country', $attributes['country'])
            ->value('id');
    }

    protected function combineBirthDate(object $legacyPerson): ?string
    {
        if ($this->legacyValue($legacyPerson, 'per_BirthYear') === null || $this->legacyValue($legacyPerson, 'per_BirthMonth') === null || $this->legacyValue($legacyPerson, 'per_BirthDay') === null) {
            return null;
        }

        if (! $this->legacyValue($legacyPerson, 'per_BirthYear') || ! $this->legacyValue($legacyPerson, 'per_BirthMonth') || ! $this->legacyValue($legacyPerson, 'per_BirthDay')) {
            return null;
        }

        return sprintf(
            '%04d-%02d-%02d',
            $this->legacyValue($legacyPerson, 'per_BirthYear'),
            $this->legacyValue($legacyPerson, 'per_BirthMonth'),
            $this->legacyValue($legacyPerson, 'per_BirthDay')
        );
    }

    protected function legacyValue(object $record, string $key, mixed $default = null): mixed
    {
        return data_get($record, $key, $default);
    }
}
