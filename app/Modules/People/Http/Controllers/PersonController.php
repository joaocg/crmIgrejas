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

final class PersonController
{
    public function index(ListPeopleRequest $request, ListPeopleAction $action): AnonymousResourceCollection
    {
        return PersonResource::collection($action->execute($request));
    }

    public function store(StorePersonRequest $request, CreatePersonAction $action): PersonResource
    {
        $person = $action->execute($request->validated());

        return PersonResource::make($person->load(PersonResource::RELATIONS));
    }

    public function show(Person $person): PersonResource
    {
        return PersonResource::make($person->load(PersonResource::RELATIONS));
    }

    public function update(UpdatePersonRequest $request, Person $person, UpdatePersonAction $action): PersonResource
    {
        $person = $action->execute($person, $request->validated());

        return PersonResource::make($person->load(PersonResource::RELATIONS));
    }

    public function destroy(Person $person, DeletePersonAction $action): JsonResponse
    {
        $action->execute($person);

        return response()->json([], 204);
    }
}
