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
use Illuminate\Validation\Rule;

final class PersonController
{
    public function index(Request $request, ListPeopleAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreatePersonAction $action): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => [
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'address_id' => [
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
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

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, Person $person): JsonResponse
    {
        $this->authorizeTenantAccess($request, $person);

        return response()->json($person->load(['family', 'address', 'contacts']));
    }

    public function update(Request $request, Person $person, UpdatePersonAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $person);

        $validated = $request->validate([
            'family_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'address_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('addresses', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
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

    public function destroy(Request $request, Person $person, DeletePersonAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $person);

        $action->execute($person);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Person $person): void
    {
        abort_unless((int) $person->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
