<?php

declare(strict_types=1);

namespace App\Modules\Care\Http\Controllers;

use App\Models\PastoralCareRecord;
use App\Modules\Care\Actions\CreatePastoralCareRecordAction;
use App\Modules\Care\Actions\DeletePastoralCareRecordAction;
use App\Modules\Care\Actions\ListPastoralCareRecordsAction;
use App\Modules\Care\Actions\UpdatePastoralCareRecordAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PastoralCareController
{
    public function index(ListPastoralCareRecordsAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreatePastoralCareRecordAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'person_id' => ['nullable', 'integer', 'exists:persons,id'],
            'family_id' => ['nullable', 'integer', 'exists:families,id'],
            'pastor_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'pastor_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:64'],
            'visible' => ['nullable', 'boolean'],
            'body' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(PastoralCareRecord $pastoralCare): JsonResponse
    {
        return response()->json($pastoralCare->load(['person', 'family', 'pastor']));
    }

    public function update(Request $request, PastoralCareRecord $pastoralCare, UpdatePastoralCareRecordAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'person_id' => ['sometimes', 'nullable', 'integer', 'exists:persons,id'],
            'family_id' => ['sometimes', 'nullable', 'integer', 'exists:families,id'],
            'pastor_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'pastor_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', 'max:64'],
            'visible' => ['sometimes', 'boolean'],
            'body' => ['sometimes', 'nullable', 'string'],
            'recorded_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json($action->execute($pastoralCare, $validated));
    }

    public function destroy(PastoralCareRecord $pastoralCare, DeletePastoralCareRecordAction $action): JsonResponse
    {
        $action->execute($pastoralCare);

        return response()->json([], 204);
    }
}
