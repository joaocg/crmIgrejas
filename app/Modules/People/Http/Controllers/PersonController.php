<?php

declare(strict_types=1);

namespace App\Modules\People\Http\Controllers;

use App\Models\Person;
use App\Modules\People\Actions\CreatePersonAction;
use App\Modules\People\Actions\DeletePersonAction;
use App\Modules\People\Actions\ListPeopleAction;
use App\Modules\People\Actions\UpdatePersonAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PersonController
{
    public function index(ListPeopleAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreatePersonAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'family_id' => ['nullable', 'integer', 'exists:families,id'],
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'suffix' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'membership_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'integer'],
            'envelope_number' => ['nullable', 'integer'],
            'newsletter_enabled' => ['nullable', 'boolean'],
            'deactivated_at' => ['nullable', 'date'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(Person $person): JsonResponse
    {
        return response()->json($person->load(['family', 'address', 'contacts']));
    }

    public function update(Request $request, Person $person, UpdatePersonAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'family_id' => ['sometimes', 'nullable', 'integer', 'exists:families,id'],
            'address_id' => ['sometimes', 'nullable', 'integer', 'exists:addresses,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'suffix' => ['sometimes', 'nullable', 'string', 'max:255'],
            'birth_date' => ['sometimes', 'nullable', 'date'],
            'membership_date' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'integer'],
            'envelope_number' => ['sometimes', 'nullable', 'integer'],
            'newsletter_enabled' => ['sometimes', 'boolean'],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json($action->execute($person, $validated));
    }

    public function destroy(Person $person, DeletePersonAction $action): JsonResponse
    {
        $action->execute($person);

        return response()->json([], 204);
    }
}
