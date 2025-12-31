<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\UpdateThirdPartyProfileRequest;
use App\Http\Requests\ThirdParty\Api\UpdateSupplierProfileRequest;
use App\Http\Requests\ThirdParty\Api\UpdateTenantProfileRequest;
use App\Http\Requests\ThirdParty\Api\UpdateCustomerProfileRequest;
use App\Http\Resources\ThirdParty\ThirdPartyUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user->load(['thirdParty.businessType', 'thirdParty.country', 'thirdParty.location']);

        return response()->json([
            'success' => true,
            'data' => new ThirdPartyUserResource($user),
            'message' => 'Profile retrieved successfully'
        ]);
    }

    public function updateProfile(UpdateThirdPartyProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $thirdParty = $user->thirdParty;

            if (!$thirdParty) {
                return response()->json([
                    'success' => false,
                    'message' => 'Third Party profile context not found'
                ], 404);
            }

            DB::beginTransaction();

            $thirdParty->update($request->validated());

            DB::commit();

            $user->load(['thirdParty.businessType', 'thirdParty.country', 'thirdParty.location']);

            return response()->json([
                'success' => true,
                'data' => new ThirdPartyUserResource($user),
                'message' => 'Profile updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => config('app.debug') ? $e->getMessage() : 'An internal error occurred'
            ], 500);
        }
    }

    public function getAvailableProfiles(Request $request): JsonResponse
    {
        $user = $request->user();
        $thirdParty = $user->thirdParty;

        if (!$thirdParty) {
            return response()->json([
                'success' => false,
                'message' => 'No third party profile found'
            ], 404);
        }

        $profiles = [];

        if ($user->isSupplier()) {
            $profiles[] = [
                'type' => 'supplier',
                'label' => 'Supplier Profile',
                'hasProfile' => $thirdParty->supplierMaster()->exists()
            ];
        }

        if ($user->isTenant()) {
            $profiles[] = [
                'type' => 'tenant',
                'label' => 'Tenant Profile',
                'hasProfile' => $thirdParty->tenantProfile()->exists()
            ];
        }

        if ($user->isCustomer()) {
            $profiles[] = [
                'type' => 'customer',
                'label' => 'Customer Profile',
                'hasProfile' => $thirdParty->customerProfile()->exists()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'availableProfiles' => $profiles,
                'totalProfiles' => count($profiles)
            ]
        ]);
    }

    public function getSupplierProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isSupplier()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a supplier profile'
            ], 404);
        }

        $supplier = $user->thirdParty->supplierMaster;

        if (!$supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier profile not found'
            ], 404);
        }

        // Load categories from the ThirdParty relationship
        $categories = $user->thirdParty->categories;

        return response()->json([
            'success' => true,
            'data' => [
                'supplierId' => $supplier->SupplierID,
                'approvalStatus' => $supplier->ApprovalStatus,
                'isPrequalified' => $supplier->IsPrequalified,
                'categories' => $categories,
                'createdOn' => $supplier->CreatedOn,
            ]
        ]);
    }

    public function getTenantProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isTenant()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a tenant profile'
            ], 404);
        }

        $tenant = $user->thirdParty->tenantProfile()->with(['type'])->first();

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenantType' => $tenant->TenantType,
                'type' => $tenant->type,
                'remarks' => $tenant->Remarks,
                'isActive' => $tenant->IsActive,
                'createdOn' => $tenant->CreatedOn,
            ]
        ]);
    }

    public function getCustomerProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'User does not have a customer profile'
            ], 404);
        }

        $customer = $user->thirdParty->customerProfile()->with(['genders', 'maritalstatus', 'occupations'])->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer profile not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'dateOfBirth' => $customer->DateOfBirth,
                'gender' => $customer->Gender,
                'genderDetail' => $customer->genders,
                'maritalStatus' => $customer->MaritalStatus,
                'maritalStatusDetail' => $customer->maritalstatus,
                'occupation' => $customer->Occupation,
                'occupationDetail' => $customer->occupations,
                'createdOn' => $customer->CreatedOn,
            ]
        ]);
    }

    public function updateSupplierProfile(UpdateSupplierProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isSupplier()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have a supplier profile'
                ], 404);
            }

            $supplier = $user->thirdParty->supplierMaster;

            if (!$supplier) {
                return response()->json([
                    'success' => false,
                    'message' => 'Supplier profile not found'
                ], 404);
            }

            DB::beginTransaction();

            $validated = $request->validated();

            // Update categories if provided
            if (isset($validated['category_ids'])) {
                $user->thirdParty->categories()->sync($validated['category_ids']);
                unset($validated['category_ids']);
            }

            // Update other supplier fields if any
            if (!empty($validated)) {
                $supplier->update($validated);
            }

            DB::commit();

            // Reload categories from ThirdParty relationship
            $user->thirdParty->load('categories');

            return response()->json([
                'success' => true,
                'data' => [
                    'supplierId' => $supplier->SupplierID,
                    'approvalStatus' => $supplier->ApprovalStatus,
                    'isPrequalified' => $supplier->IsPrequalified,
                    'categories' => $user->thirdParty->categories,
                ],
                'message' => 'Supplier profile updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update supplier profile',
                'error' => config('app.debug') ? $e->getMessage() : 'An internal error occurred'
            ], 500);
        }
    }

    public function updateTenantProfile(UpdateTenantProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isTenant()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have a tenant profile'
                ], 404);
            }

            $tenant = $user->thirdParty->tenantProfile;

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant profile not found'
                ], 404);
            }

            DB::beginTransaction();

            $tenant->update($request->validated());

            DB::commit();

            $tenant->load(['type']);

            return response()->json([
                'success' => true,
                'data' => [
                    'tenantType' => $tenant->TenantType,
                    'type' => $tenant->type,
                    'remarks' => $tenant->Remarks,
                    'isActive' => $tenant->IsActive,
                ],
                'message' => 'Tenant profile updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update tenant profile',
                'error' => config('app.debug') ? $e->getMessage() : 'An internal error occurred'
            ], 500);
        }
    }

    public function updateCustomerProfile(UpdateCustomerProfileRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->isCustomer()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User does not have a customer profile'
                ], 404);
            }

            $customer = $user->thirdParty->customerProfile;

            if (!$customer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Customer profile not found'
                ], 404);
            }

            DB::beginTransaction();

            $customer->update($request->validated());

            DB::commit();

            $customer->load(['genders', 'maritalstatus', 'occupations']);

            return response()->json([
                'success' => true,
                'data' => [
                    'dateOfBirth' => $customer->DateOfBirth,
                    'gender' => $customer->Gender,
                    'genderDetail' => $customer->genders,
                    'maritalStatus' => $customer->MaritalStatus,
                    'maritalStatusDetail' => $customer->maritalstatus,
                    'occupation' => $customer->Occupation,
                    'occupationDetail' => $customer->occupations,
                ],
                'message' => 'Customer profile updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer profile',
                'error' => config('app.debug') ? $e->getMessage() : 'An internal error occurred'
            ], 500);
        }
    }
}
