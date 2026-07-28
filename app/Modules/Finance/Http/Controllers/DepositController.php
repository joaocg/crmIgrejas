<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Models\Deposit;
use App\Modules\Finance\Actions\CreateDepositAction;
use App\Modules\Finance\Actions\DeleteDepositAction;
use App\Modules\Finance\Actions\ListDepositsAction;
use App\Modules\Finance\Actions\UpdateDepositAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class DepositController
{
    public function index(Request $request, ListDepositsAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreateDepositAction $action): JsonResponse
    {
        $validated = $request->validate([
            'fund_id' => [
                'nullable',
                'integer',
                Rule::exists('donation_funds', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'date' => ['required', 'date'],
            'comment' => ['nullable', 'string'],
            'closed' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:32'],
            'entered_by_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
        ]);

        $validated['entered_by_user_id'] ??= $request->user()->id;

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, Deposit $deposit): JsonResponse
    {
        $this->authorizeTenantAccess($request, $deposit);

        return response()->json($deposit->load(['fund', 'enteredBy', 'pledges']));
    }

    public function update(Request $request, Deposit $deposit, UpdateDepositAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $deposit);

        $validated = $request->validate([
            'fund_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('donation_funds', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'date' => ['sometimes', 'date'],
            'comment' => ['sometimes', 'nullable', 'string'],
            'closed' => ['sometimes', 'boolean'],
            'type' => ['sometimes', 'nullable', 'string', 'max:32'],
            'entered_by_user_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
        ]);

        return response()->json($action->execute($deposit, $validated));
    }

    public function destroy(Request $request, Deposit $deposit, DeleteDepositAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $deposit);

        $action->execute($deposit);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Deposit $deposit): void
    {
        abort_unless((int) $deposit->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
