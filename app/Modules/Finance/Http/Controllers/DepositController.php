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

final class DepositController
{
    public function index(ListDepositsAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreateDepositAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'fund_id' => ['nullable', 'integer', 'exists:donation_funds,id'],
            'date' => ['required', 'date'],
            'comment' => ['nullable', 'string'],
            'closed' => ['nullable', 'boolean'],
            'type' => ['nullable', 'string', 'max:32'],
            'entered_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(Deposit $deposit): JsonResponse
    {
        return response()->json($deposit->load(['fund', 'enteredBy', 'pledges']));
    }

    public function update(Request $request, Deposit $deposit, UpdateDepositAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'fund_id' => ['sometimes', 'nullable', 'integer', 'exists:donation_funds,id'],
            'date' => ['sometimes', 'date'],
            'comment' => ['sometimes', 'nullable', 'string'],
            'closed' => ['sometimes', 'boolean'],
            'type' => ['sometimes', 'nullable', 'string', 'max:32'],
            'entered_by_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
        ]);

        return response()->json($action->execute($deposit, $validated));
    }

    public function destroy(Deposit $deposit, DeleteDepositAction $action): JsonResponse
    {
        $action->execute($deposit);

        return response()->json([], 204);
    }
}
