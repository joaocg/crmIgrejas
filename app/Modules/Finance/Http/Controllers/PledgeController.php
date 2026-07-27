<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Models\Pledge;
use App\Modules\Finance\Actions\CreatePledgeAction;
use App\Modules\Finance\Actions\DeletePledgeAction;
use App\Modules\Finance\Actions\ListPledgesAction;
use App\Modules\Finance\Actions\UpdatePledgeAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PledgeController
{
    public function index(ListPledgesAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreatePledgeAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'family_id' => ['required', 'integer', 'exists:families,id'],
            'fund_id' => ['nullable', 'integer', 'exists:donation_funds,id'],
            'deposit_id' => ['nullable', 'integer', 'exists:deposits,id'],
            'fiscal_year' => ['nullable', 'integer'],
            'pledged_on' => ['nullable', 'date'],
            'amount' => ['required', 'numeric'],
            'schedule' => ['nullable', 'string', 'max:32'],
            'payment_method' => ['nullable', 'string', 'max:32'],
            'check_number' => ['nullable', 'string', 'max:64'],
            'status' => ['nullable', 'string', 'max:32'],
            'notes' => ['nullable', 'string'],
            'non_deductible_amount' => ['nullable', 'numeric'],
            'payment_type' => ['nullable', 'string', 'max:32'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(Pledge $pledge): JsonResponse
    {
        return response()->json($pledge->load(['family', 'fund', 'deposit']));
    }

    public function update(Request $request, Pledge $pledge, UpdatePledgeAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'family_id' => ['sometimes', 'integer', 'exists:families,id'],
            'fund_id' => ['sometimes', 'nullable', 'integer', 'exists:donation_funds,id'],
            'deposit_id' => ['sometimes', 'nullable', 'integer', 'exists:deposits,id'],
            'fiscal_year' => ['sometimes', 'nullable', 'integer'],
            'pledged_on' => ['sometimes', 'nullable', 'date'],
            'amount' => ['sometimes', 'numeric'],
            'schedule' => ['sometimes', 'nullable', 'string', 'max:32'],
            'payment_method' => ['sometimes', 'nullable', 'string', 'max:32'],
            'check_number' => ['sometimes', 'nullable', 'string', 'max:64'],
            'status' => ['sometimes', 'nullable', 'string', 'max:32'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'non_deductible_amount' => ['sometimes', 'nullable', 'numeric'],
            'payment_type' => ['sometimes', 'nullable', 'string', 'max:32'],
        ]);

        return response()->json($action->execute($pledge, $validated));
    }

    public function destroy(Pledge $pledge, DeletePledgeAction $action): JsonResponse
    {
        $action->execute($pledge);

        return response()->json([], 204);
    }
}
