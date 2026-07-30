<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Controllers;

use App\Models\Family;
use App\Modules\Families\Actions\CreateFamilyAction;
use App\Modules\Families\Actions\DeleteFamilyAction;
use App\Modules\Families\Actions\ListFamiliesAction;
use App\Modules\Families\Actions\UpdateFamilyAction;
use App\Modules\Families\Http\Requests\ListFamiliesRequest;
use App\Modules\Families\Http\Requests\StoreFamilyRequest;
use App\Modules\Families\Http\Requests\UpdateFamilyRequest;
use App\Modules\Families\Http\Resources\FamilyResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class FamilyController
{
    public function index(ListFamiliesRequest $request, ListFamiliesAction $action): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Family::class);

        return FamilyResource::collection($action->execute($request));
    }

    public function store(StoreFamilyRequest $request, CreateFamilyAction $action): FamilyResource
    {
        Gate::authorize('create', Family::class);

        $family = $action->execute($request->validated());

        return FamilyResource::make($family->load(Family::API_RELATIONS));
    }

    public function show(Family $family): FamilyResource
    {
        Gate::authorize('view', $family);

        return FamilyResource::make($family->load(Family::API_RELATIONS));
    }

    public function update(UpdateFamilyRequest $request, Family $family, UpdateFamilyAction $action): FamilyResource
    {
        Gate::authorize('update', $family);

        $family = $action->execute($family, $request->validated());

        return FamilyResource::make($family->load(Family::API_RELATIONS));
    }

    public function destroy(Family $family, DeleteFamilyAction $action): JsonResponse
    {
        Gate::authorize('delete', $family);

        $action->execute($family);

        return response()->json([], 204);
    }
}
