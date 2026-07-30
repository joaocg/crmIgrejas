<?php

declare(strict_types=1);

namespace App\Policies;

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
 * Deliberate divergence — viewAny. PeopleGroupController.php:90-105 lets any
 * authenticated user call the list and returns
 * `GroupQuery::create()->findById($ids)` with `$ids =
 * getGroupManagerIds()` (User.php:577-588) for anybody who is not
 * admin/manageGroups. That row narrowing needs the same missing person link,
 * so viewAny falls back to ModulePolicy's `navigation.groups` gate — the same
 * gate People and Families use — and returns every group in the tenant to
 * whoever passes it. This is the one place the port is *less* restrictive than
 * the legacy for a non-manager who nonetheless holds `navigation.groups`;
 * flagged for the controller rather than silently narrowing the list to zero
 * rows, which would leave the screen unusable for exactly the users the legacy
 * served.
 */
final class GroupPolicy extends ModulePolicy
{
    protected function abilityPrefix(): string
    {
        return 'groups';
    }

    protected function navigationAbility(): string
    {
        return 'navigation.groups';
    }
}
