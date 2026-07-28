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
use Illuminate\Validation\Rule;

final class PastoralCareController
{
    public function index(Request $request, ListPastoralCareRecordsAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreatePastoralCareRecordAction $action): JsonResponse
    {
        $validated = $request->validate([
            'person_id' => [
                'nullable',
                'integer',
                Rule::exists('persons', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'family_id' => [
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'pastor_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'pastor_name' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:64'],
            'visible' => ['nullable', 'boolean'],
            'body' => ['nullable', 'string'],
            'recorded_at' => ['nullable', 'date'],
        ]);

        $validated['pastor_user_id'] ??= $request->user()->id;
        $validated['recorded_at'] ??= now();

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, PastoralCareRecord $pastoralCare): JsonResponse
    {
        $this->authorizeTenantAccess($request, $pastoralCare);

        return response()->json($pastoralCare->load(['person', 'family', 'pastor']));
    }

    public function update(Request $request, PastoralCareRecord $pastoralCare, UpdatePastoralCareRecordAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $pastoralCare);

        $validated = $request->validate([
            'person_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('persons', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'family_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'pastor_user_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'pastor_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'type' => ['sometimes', 'nullable', 'string', 'max:64'],
            'visible' => ['sometimes', 'boolean'],
            'body' => ['sometimes', 'nullable', 'string'],
            'recorded_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json($action->execute($pastoralCare, $validated));
    }

    public function destroy(Request $request, PastoralCareRecord $pastoralCare, DeletePastoralCareRecordAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $pastoralCare);

        $action->execute($pastoralCare);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, PastoralCareRecord $pastoralCare): void
    {
        abort_unless((int) $pastoralCare->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
