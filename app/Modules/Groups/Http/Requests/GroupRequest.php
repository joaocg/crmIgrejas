<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Requests;

use App\Support\Http\Requests\BuildsCrudRules;
use App\Support\Validation\TenantRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared field constraints for StoreGroupRequest and UpdateGroupRequest.
 *
 * Each concrete request declares only how presence is handled (required on
 * create vs. sometimes-optional on update) by combining typeRules() with the
 * forCreate()/forUpdate() helpers from BuildsCrudRules. Mirrors
 * App\Modules\Families\Http\Requests\FamilyRequest.
 *
 * Legacy rule: src/EcclesiaCRM/APIControllers/PeopleGroupController.php:340-355
 * (newGroup) only ever requires a group name — `$group->setName(...)` is the
 * single mandatory field, the type is derived — and lines 373-374
 * (updateGroup) reject the request unless groupName, groupType and description
 * are all present. `name` is therefore the only field required on create.
 */
abstract class GroupRequest extends FormRequest
{
    use BuildsCrudRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field => type/format constraints, without presence modifiers
     * (required/nullable/sometimes).
     *
     * `role_id` uses TenantRule::exists() and not Rule::exists(): the latter
     * builds its own query builder and so bypasses the global TenantScope,
     * which would let a request point a group at another tenant's role.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function typeRules(): array
    {
        return [
            'role_id' => ['integer', TenantRule::exists('roles')],
            'type' => ['string', 'max:50'],
            'name' => ['string', 'max:255'],
            'description' => ['string'],
            'has_special_properties' => ['boolean'],
            'is_active' => ['boolean'],
            'include_in_email_export' => ['boolean'],
        ];
    }
}
