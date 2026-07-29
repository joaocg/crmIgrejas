<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Controllers;

use App\Models\Person;
use App\Modules\People\Actions\CreatePersonAction;
use App\Modules\People\Actions\DeletePersonAction;
use App\Modules\People\Actions\ListPeopleAction;
use App\Modules\People\Actions\UpdatePersonAction;
use App\Modules\People\Http\Requests\ListPeopleRequest;
use App\Modules\People\Http\Requests\StorePersonRequest;
use App\Modules\People\Http\Requests\UpdatePersonRequest;
use App\Modules\People\Http\Resources\PersonResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class PersonController
{
    public function index(ListPeopleRequest $request, ListPeopleAction $action): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Person::class);

        return PersonResource::collection($action->execute($request));
    }

    public function store(StorePersonRequest $request, CreatePersonAction $action): PersonResource
    {
        Gate::authorize('create', Person::class);

        $person = $action->execute($request->validated());

        return PersonResource::make($person->load(Person::API_RELATIONS));
    }

    public function show(Person $person): PersonResource
    {
        Gate::authorize('view', $person);

        return PersonResource::make($person->load(Person::API_RELATIONS));
    }

    public function update(UpdatePersonRequest $request, Person $person, UpdatePersonAction $action): PersonResource
    {
        Gate::authorize('update', $person);

        $person = $action->execute($person, $request->validated());

        return PersonResource::make($person->load(Person::API_RELATIONS));
    }

    public function destroy(Person $person, DeletePersonAction $action): JsonResponse
    {
        Gate::authorize('delete', $person);

        $action->execute($person);

        return response()->json([], 204);
    }
}
