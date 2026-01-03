<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\{
    UpdateThirdPartyProfileRequest,
    UpdateSupplierProfileRequest
};
use App\Http\Resources\ThirdParty\Api\{
    ThirdPartyUserResource,
    SupplierProfileResource,
    TenantProfileResource,
    CustomerProfileResource
};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB};

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user()->load(['thirdParty.businessType', 'thirdParty.country', 'thirdParty.location']);

        return response()->json([
            'success' => true,
            'data' => new ThirdPartyUserResource($user),
            'message' => 'Profile retrieved successfully'
        ]);
    }

    public function updateProfile(UpdateThirdPartyProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json(['success' => false, 'message' => 'Context not found'], 404);
        }

        DB::transaction(fn() => $thirdParty->update($request->validated()));

        return response()->json([
            'success' => true,
            'data' => new ThirdPartyUserResource($user->refresh()->load(['thirdParty.businessType', 'thirdParty.country'])),
            'message' => 'Profile updated successfully'
        ]);
    }

    public function getAvailableProfiles(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'thirdParty.supplierMaster',
            'thirdParty.tenantProfile',
            'thirdParty.customerProfile'
        ]);

        $tp = $user->thirdParty;

        $profiles = collect([
            [
                'type' => 'base',
                'label' => 'General Profile',
                'hasProfile' => true
            ],
            [
                'type' => 'supplier',
                'label' => 'Supplier Profile',
                'hasProfile' => $tp ? (bool)$tp->supplierMaster : false
            ],
            [
                'type' => 'tenant',
                'label' => 'Tenant Profile',
                'hasProfile' => $tp ? (bool)$tp->tenantProfile : false
            ],
            [
                'type' => 'customer',
                'label' => 'Customer Profile',
                'hasProfile' => $tp ? (bool)$tp->customerProfile : false
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'availableProfiles' => $profiles,
                'totalActive' => $profiles->where('hasProfile', true)->count()
            ]
        ]);
    }

    public function getSupplierProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.supplierMaster', 'thirdParty.categories');
        $supplier = $user->thirdParty->supplierMaster;

        if (!$supplier) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SupplierProfileResource($supplier)
        ]);
    }

    public function getTenantProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.tenantProfile.type');
        $tenant = $user->thirdParty->tenantProfile;

        if (!$tenant) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TenantProfileResource($tenant)
        ]);
    }

    public function getCustomerProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.customerProfile.genders', 'thirdParty.customerProfile.maritalstatus');
        $customer = $user->thirdParty->customerProfile;

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CustomerProfileResource($customer)
        ]);
    }

    public function updateSupplierProfile(UpdateSupplierProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $supplier = $user->thirdParty->supplierMaster;

        DB::transaction(function () use ($user, $supplier, $request) {
            $validated = $request->validated();
            if (isset($validated['category_ids'])) {
                $user->thirdParty->categories()->sync($validated['category_ids']);
                unset($validated['category_ids']);
            }
            if (!empty($validated)) {
                $supplier->update($validated);
            }
        });

        return response()->json([
            'success' => true,
            'data' => new SupplierProfileResource($supplier->refresh()),
            'message' => 'Supplier profile updated'
        ]);
    }
}
