<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Support\Authorization\ModulePolicy;

/**
 * Legacy flags, per endpoint:
 *
 * - create: src/EcclesiaCRM/APIControllers/PeopleGroupController.php:342 —
 *   `if (!SessionUser::getUser()->isManageGroupsEnabled()) return 401;`. The UI
 *   agrees: src/v2/templates/group/grouplist.php:144 only renders the "Add
 *   New" button inside `if (SessionUser::getUser()->isManageGroupsEnabled())`.
 * - delete: PeopleGroupController.php:420 — the same
 *   `isManageGroupsEnabled()` guard.
 * - update: PeopleGroupController.php:373 —
 *   `isGroupManagerEnabledForId($args['groupID'])`, and
 *   VIEWGroupController.php:211 gates the whole group editor screen on
 *   `isGroupManagerEnabled()`.
 * - view/viewAny: PeopleGroupController.php:405 guards a single group with
 *   `isGroupManagerEnabledForId()`; the list at :90-105 has no guard at all,
 *   it narrows the *rows* instead (see the divergence below).
 *
 * src/EcclesiaCRM/model/EcclesiaCRM/User.php:633-636 defines
 * `isManageGroupsEnabled() = isAdmin() || isManageGroups()`, a pure role flag,
 * which maps onto the `groups.create` / `groups.update` / `groups.delete`
 * abilities of the new role model (`*` standing in for isAdmin()).
 *
 * Deliberate divergence — the per-group manager disjunct is NOT ported.
 * User.php:605-619 defines `isGroupManagerEnabledForId($groupId)` as
 * `isManageGroups() || the user's person has a group_grp_manager row for that
 * group`. Only the first disjunct is ported, because the new `users` table has
 * no person link, so there is no way to resolve the current user to the
 * `group_memberships.is_manager` rows that would answer the second. The
 * divergence is strictly more restrictive: a user who was a manager of one
 * group but holds no group role now needs the role ability. Restore the second
 * disjunct — as a `before()`/override on update and view — once users gain a
 * person_id.
 *
 * Deliberate divergence — viewAny. PeopleGroupController.php:90-105 branches:
 * `isAdmin() || isManageGroups()` gets every group, and everyone else gets
 * `GroupQuery::create()->findById(getGroupManagerIds())` (User.php:577-588),
 * i.e. only the groups they personally manage. Just one of those two branches
 * is computable here: the row narrowing needs the person link the new `users`
 * table does not have.
 *
 * So viewAny ports the first branch literally — `groups.view_all` is the new
 * name for `isAdmin() || isManageGroups()` — and a user without it gets a 403
 * where the legacy would have shown them a narrowed list. That is the
 * restrictive direction, chosen deliberately: returning every group in the
 * tenant to anyone holding `navigation.groups` would show non-managers groups
 * the legacy hid from them, and a permission that has been loosened in
 * production cannot be tightened again without breaking someone. Granting
 * `groups.view_all` is an admin's one-line decision in the meantime.
 *
 * When users gain a person_id, replace the 403 with the narrowed query rather
 * than widening this gate.
 */
final class GroupPolicy extends ModulePolicy
{
    /**
     * The new name for the legacy `isAdmin() || isManageGroups()` branch of
     * PeopleGroupController::getAllGroups().
     */
    public const VIEW_ALL_ABILITY = 'groups.view_all';

    public function viewAny(User $user): bool
    {
        return parent::viewAny($user) && $this->allows($user, self::VIEW_ALL_ABILITY);
    }

    protected function abilityPrefix(): string
    {
        return 'groups';
    }

    protected function navigationAbility(): string
    {
        return 'navigation.groups';
    }
}
