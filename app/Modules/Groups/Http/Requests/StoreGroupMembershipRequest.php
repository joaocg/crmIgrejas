<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Requests;

use App\Support\Validation\TenantRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Legacy rule: src/api/routes/people/people-groups.php:127 attaches a person
 * to a group as `POST /groups/{groupID}/addperson/{personID}`, and
 * src/EcclesiaCRM/APIControllers/PeopleGroupController.php:559-582 reads only
 * the optional `RoleID` from the body, falling back to the group's default
 * role. Everything else on the new `group_memberships` row (joined_at,
 * left_at, is_manager) has no counterpart in that legacy payload and stays
 * optional here.
 *
 * `person_id` and `role_id` use TenantRule::exists() rather than
 * Rule::exists(): the latter builds its own query builder and bypasses the
 * global TenantScope, so a bare one would let a request pull another tenant's
 * person into this tenant's group.
 */
final class StoreGroupMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'person_id' => ['required', 'integer', TenantRule::exists('persons')],
            'role_id' => ['nullable', 'integer', TenantRule::exists('roles')],
            'joined_at' => ['nullable', 'date'],
            'left_at' => ['nullable', 'date'],
            'is_manager' => ['nullable', 'boolean'],
        ];
    }
}
