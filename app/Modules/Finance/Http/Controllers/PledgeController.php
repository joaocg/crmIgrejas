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
use Illuminate\Validation\Rule;

final class PledgeController
{
    public function index(Request $request, ListPledgesAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreatePledgeAction $action): JsonResponse
    {
        $validated = $request->validate([
            'family_id' => [
                'required',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'fund_id' => [
                'nullable',
                'integer',
                Rule::exists('donation_funds', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'deposit_id' => [
                'nullable',
                'integer',
                Rule::exists('deposits', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
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

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, Pledge $pledge): JsonResponse
    {
        $this->authorizeTenantAccess($request, $pledge);

        return response()->json($pledge->load(['family', 'fund', 'deposit']));
    }

    public function update(Request $request, Pledge $pledge, UpdatePledgeAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $pledge);

        $validated = $request->validate([
            'family_id' => [
                'sometimes',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'fund_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('donation_funds', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'deposit_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('deposits', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
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

    public function destroy(Request $request, Pledge $pledge, DeletePledgeAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $pledge);

        $action->execute($pledge);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Pledge $pledge): void
    {
        abort_unless((int) $pledge->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
