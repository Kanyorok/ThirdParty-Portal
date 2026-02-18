<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Helpers\SystemHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\Api\{
    UpdateCustomerProfileRequest,
    UpdateSupplierProfileRequest,
    UpdateTenantProfileRequest,
    UpdateThirdPartyProfileRequest
};
use App\Http\Resources\ThirdParty\Api\{
    CustomerProfileResource,
    SupplierProfileResource,
    TenantProfileResource,
    ThirdPartyUserResource
};
use App\Models\DMS\Image;
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
            'message' => 'Profile retrieved successfully',
        ]);
    }

    public function updateProfile(UpdateThirdPartyProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $thirdParty = $user->thirdParty;

        if (! $thirdParty) {
            return response()->json(['success' => false, 'message' => 'Context not found'], 404);
        }

        DB::transaction(fn () => $thirdParty->update($request->validated()));

        return response()->json([
            'success' => true,
            'data' => new ThirdPartyUserResource($user->refresh()->load(['thirdParty.businessType', 'thirdParty.country'])),
            'message' => 'Profile updated successfully',
        ]);
    }

    public function getLogo(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.photo');
        $thirdParty = $user->thirdParty;

        if (! $thirdParty) {
            return response()->json(['success' => false, 'message' => 'Context not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'logo' => $this->imagePayload($thirdParty->photo),
                'imageId' => $thirdParty->ImageId,
            ],
            'message' => 'Logo retrieved successfully',
        ]);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'logo' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();
        $thirdParty = $user->thirdParty;

        if (! $thirdParty) {
            return response()->json(['success' => false, 'message' => 'Context not found'], 404);
        }

        DB::transaction(fn () => $thirdParty->setImage($validated['logo'], SystemHelper::user(), 'ImageId'));
        $thirdParty->refresh()->load('photo');

        return response()->json([
            'success' => true,
            'data' => [
                'logo' => $this->imagePayload($thirdParty->photo),
                'imageId' => $thirdParty->ImageId,
            ],
            'message' => 'Logo uploaded successfully',
        ]);
    }

    public function getUserImage(Request $request): JsonResponse
    {
        $user = $request->user()->load('photo');

        return response()->json([
            'success' => true,
            'data' => [
                'image' => $this->imagePayload($user->photo),
                'imageId' => $user->ImageId,
            ],
            'message' => 'User image retrieved successfully',
        ]);
    }

    public function uploadUserImage(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ]);

        $user = $request->user();

        DB::transaction(fn () => $user->setImage($validated['image'], SystemHelper::user(), 'ImageId'));
        $user->refresh()->load('photo');

        return response()->json([
            'success' => true,
            'data' => [
                'image' => $this->imagePayload($user->photo),
                'imageId' => $user->ImageId,
            ],
            'message' => 'User image uploaded successfully',
        ]);
    }

    public function getAvailableProfiles(Request $request): JsonResponse
    {
        $user = $request->user()->load([
            'thirdParty.supplierMaster',
            'thirdParty.tenantProfile',
            'thirdParty.customerProfile',
        ]);

        $tp = $user->thirdParty;

        $profiles = collect([
            [
                'type' => 'base',
                'label' => 'General Profile',
                'hasProfile' => true,
            ],
            [
                'type' => 'supplier',
                'label' => 'Supplier Profile',
                'hasProfile' => $tp ? (bool)$tp->supplierMaster : false,
            ],
            [
                'type' => 'tenant',
                'label' => 'Tenant Profile',
                'hasProfile' => $tp ? (bool)$tp->tenantProfile : false,
            ],
            [
                'type' => 'customer',
                'label' => 'Customer Profile',
                'hasProfile' => $tp ? (bool)$tp->customerProfile : false,
            ],
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'availableProfiles' => $profiles,
                'totalActive' => $profiles->where('hasProfile', true)->count(),
            ],
        ]);
    }

    public function getSupplierProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.supplierMaster', 'thirdParty.categories');
        $supplier = $user->thirdParty->supplierMaster;

        if (! $supplier) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new SupplierProfileResource($supplier),
        ]);
    }

    public function getTenantProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.tenantProfile.type');
        $tenant = $user->thirdParty->tenantProfile;

        if (! $tenant) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new TenantProfileResource($tenant),
        ]);
    }

    public function getCustomerProfile(Request $request): JsonResponse
    {
        $user = $request->user()->load('thirdParty.customerProfile.genders', 'thirdParty.customerProfile.maritalstatus');
        $customer = $user->thirdParty->customerProfile;

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Profile not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CustomerProfileResource($customer),
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
            if (! empty($validated)) {
                $supplier->update($validated);
            }
        });

        return response()->json([
            'success' => true,
            'data' => new SupplierProfileResource($supplier->refresh()),
            'message' => 'Supplier profile updated',
        ]);
    }

    public function updateTenantProfile(UpdateTenantProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $tenant = $user->thirdParty->tenantProfile;

        if (! $tenant) {
            return response()->json(['success' => false, 'message' => 'Tenant profile not found'], 404);
        }

        DB::transaction(fn () => $tenant->update($request->validated()));

        return response()->json([
            'success' => true,
            'data' => new TenantProfileResource($tenant->refresh()),
            'message' => 'Tenant profile updated',
        ]);
    }

    public function updateCustomerProfile(UpdateCustomerProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $customer = $user->thirdParty->customerProfile;

        if (! $customer) {
            return response()->json(['success' => false, 'message' => 'Customer profile not found'], 404);
        }

        DB::transaction(fn () => $customer->update($request->validated()));

        return response()->json([
            'success' => true,
            'data' => new CustomerProfileResource($customer->refresh()),
            'message' => 'Customer profile updated',
        ]);
    }

    private function imagePayload(?Image $image): ?array
    {
        if (! $image) {
            return null;
        }

        return [
            'id' => $image->ImageID,
            'name' => $image->Name,
            'mimeType' => $image->MIMEType,
            'src' => $image->image_src,
        ];
    }
}
