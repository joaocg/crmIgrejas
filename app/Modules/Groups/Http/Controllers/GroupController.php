<?php

declare(strict_types=1);

namespace App\Modules\Groups\Http\Controllers;

use App\Models\Group;
use App\Modules\Groups\Actions\CreateGroupAction;
use App\Modules\Groups\Actions\DeleteGroupAction;
use App\Modules\Groups\Actions\ListGroupsAction;
use App\Modules\Groups\Actions\UpdateGroupAction;
use App\Modules\Groups\Http\Requests\ListGroupsRequest;
use App\Modules\Groups\Http\Requests\StoreGroupRequest;
use App\Modules\Groups\Http\Requests\UpdateGroupRequest;
use App\Modules\Groups\Http\Resources\GroupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class GroupController
{
    public function index(ListGroupsRequest $request, ListGroupsAction $action): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Group::class);

        return GroupResource::collection($action->execute($request));
    }

    public function store(StoreGroupRequest $request, CreateGroupAction $action): GroupResource
    {
        Gate::authorize('create', Group::class);

        $group = $action->execute($request->validated());

        return GroupResource::make($this->hydrate($group));
    }

    public function show(Group $group): GroupResource
    {
        Gate::authorize('view', $group);

        return GroupResource::make($this->hydrate($group));
    }

    public function update(UpdateGroupRequest $request, Group $group, UpdateGroupAction $action): GroupResource
    {
        Gate::authorize('update', $group);

        $group = $action->execute($group, $request->validated());

        return GroupResource::make($this->hydrate($group));
    }

    public function destroy(Group $group, DeleteGroupAction $action): JsonResponse
    {
        Gate::authorize('delete', $group);

        $action->execute($group);

        return response()->json([], 204);
    }

    /**
     * GroupResource reads `members` through whenLoaded() and `members_count`
     * through whenCounted(); both return MissingValue — and are stripped from
     * the JSON entirely, key and all — when the relation/aggregate is absent.
     * Every single-model endpoint therefore hydrates exactly the same set the
     * list query does.
     */
    private function hydrate(Group $group): Group
    {
        return $group->load(Group::API_RELATIONS)->loadCount(Group::API_COUNTS);
    }
}
