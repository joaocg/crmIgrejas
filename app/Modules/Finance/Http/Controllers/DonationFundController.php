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
    public function index(ListDonationFundsAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreateDonationFundAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(DonationFund $donationFund): JsonResponse
    {
        return response()->json($donationFund->load(['deposits', 'pledges']));
    }

    public function update(Request $request, DonationFund $donationFund, UpdateDonationFundAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($action->execute($donationFund, $validated));
    }

    public function destroy(DonationFund $donationFund, DeleteDonationFundAction $action): JsonResponse
    {
        $action->execute($donationFund);

        return response()->json([], 204);
    }
}
