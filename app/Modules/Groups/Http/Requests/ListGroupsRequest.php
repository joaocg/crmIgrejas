<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Requests;

use App\Support\Http\Requests\IndexRequest;

final class ListGroupsRequest extends IndexRequest
{
    /**
     * Legacy rule: the group list table
     * (src/v2/templates/group/grouplist.php:152) is rendered empty and filled
     * by src/skin/js/group/GroupList.js, whose `columns` array
     * (GroupList.js:71-137) declares exactly four columns:
     *
     *   0. "Group Name"        data: 'Name'         (GroupList.js:74-75)
     *   1. "Members"           data: 'memberCount'  (GroupList.js:92-94)
     *   2. "Group Cart Status" data: 'Id'           (GroupList.js:102-104)
     *   3. "Group Type"        data: 'groupType'    (GroupList.js:126-129)
     *
     * No column is marked `orderable: false` and the table sets no `order`
     * option, so DataTables leaves every column sortable. Three of the four
     * map onto the new schema and are allow-listed: Name -> `name`, Members ->
     * the `members_count` alias from Group::API_COUNTS, Group Type ->
     * `type`. ("groupType" is `list_lst.lst_OptionName`, injected by
     * src/EcclesiaCRM/model/EcclesiaCRM/GroupQuery.php:43; the new schema
     * flattens that lookup into the `groups.type` string column, which is what
     * resources/js/pages/modules/groups/GroupListPage.vue:18 renders under the
     * same "Type" header.)
     *
     * "Group Cart Status" is deliberately NOT allow-listed: it renders the
     * cart membership of the row (GroupList.js:105-121) against
     * `window.CRM.groupsInCart`, and the cart module has not been migrated, so
     * there is no column to sort on.
     *
     * Unlike ListPeopleRequest / ListFamiliesRequest this allow-list is NOT
     * user-dependent. Rule checked explicitly: grouplist.php contains no
     * isSeePrivacyDataEnabled() branch at all (the only two occurrences under
     * src/v2/templates/group/ are groupview.php:409 and :524, which gate a
     * *person's* group-specific properties and the members table inside the
     * single-group view, not any column of the group list). The list also has
     * no Created/Edited columns, which is why `created_at`/`updated_at` are
     * not sortable here for anyone — an unprivileged ?sort=created_at gets the
     * standard 422 for an unknown sort column.
     *
     * @return array<int, string>
     */
    protected function sortableColumns(): array
    {
        return ['name', 'members_count', 'type'];
    }

    /**
     * Legacy rule: src/skin/js/group/GroupList.js:51-138 passes no `order`
     * option to DataTables, and src/skin/js/DataTables.js sets no global
     * default either, so DataTables falls back to its documented default of
     * `[[0, 'asc']]` — column 0 being "Group Name" (GroupList.js:74). The
     * server side agrees: every list-shaped legacy group query orders by name
     * (VIEWGroupController.php:288 `GroupQuery::Create()->orderByName()`,
     * PeopleGroupController.php:111 for the default group).
     */
    protected function defaultSort(): string
    {
        return 'name';
    }
}
