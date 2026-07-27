<?php

declare(strict_types=1);

namespace App\Modules\Families\Http\Controllers;

use App\Models\Family;
use App\Modules\Families\Actions\CreateFamilyAction;
use App\Modules\Families\Actions\DeleteFamilyAction;
use App\Modules\Families\Actions\ListFamiliesAction;
use App\Modules\Families\Actions\UpdateFamilyAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class FamilyController
{
    public function index(ListFamiliesAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreateFamilyAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'address_id' => ['nullable', 'integer', 'exists:addresses,id'],
            'name' => ['required', 'string', 'max:255'],
            'wedding_date' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:255'],
            'home_phone' => ['nullable', 'string', 'max:30'],
            'work_phone' => ['nullable', 'string', 'max:30'],
            'mobile_phone' => ['nullable', 'string', 'max:30'],
            'envelope_number' => ['nullable', 'integer'],
            'newsletter_enabled' => ['nullable', 'boolean'],
            'canvass_allowed' => ['nullable', 'boolean'],
            'deactivated_at' => ['nullable', 'date'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(Family $family): JsonResponse
    {
        return response()->json($family->load(['address', 'people']));
    }

    public function update(Request $request, Family $family, UpdateFamilyAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'address_id' => ['sometimes', 'nullable', 'integer', 'exists:addresses,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'wedding_date' => ['sometimes', 'nullable', 'date'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'home_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'work_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'mobile_phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'envelope_number' => ['sometimes', 'nullable', 'integer'],
            'newsletter_enabled' => ['sometimes', 'boolean'],
            'canvass_allowed' => ['sometimes', 'boolean'],
            'deactivated_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json($action->execute($family, $validated));
    }

    public function destroy(Family $family, DeleteFamilyAction $action): JsonResponse
    {
        $action->execute($family);

        return response()->json([], 204);
    }
}
