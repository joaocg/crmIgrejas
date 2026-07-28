<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Controllers;

use App\Models\DonationFund;
use App\Modules\Finance\Actions\CreateDonationFundAction;
use App\Modules\Finance\Actions\DeleteDonationFundAction;
use App\Modules\Finance\Actions\ListDonationFundsAction;
use App\Modules\Finance\Actions\UpdateDonationFundAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DonationFundController
{
    public function index(Request $request, ListDonationFundsAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreateDonationFundAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, DonationFund $donationFund): JsonResponse
    {
        $this->authorizeTenantAccess($request, $donationFund);

        return response()->json($donationFund->load(['deposits', 'pledges']));
    }

    public function update(Request $request, DonationFund $donationFund, UpdateDonationFundAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $donationFund);

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($action->execute($donationFund, $validated));
    }

    public function destroy(Request $request, DonationFund $donationFund, DeleteDonationFundAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $donationFund);

        $action->execute($donationFund);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, DonationFund $donationFund): void
    {
        abort_unless((int) $donationFund->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
