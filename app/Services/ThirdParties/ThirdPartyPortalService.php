<?php

namespace App\Services\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Exceptions\ErroredException;
use App\Http\Requests\ThirdParty\CreateThirdPartyProfileRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyUser;
use Illuminate\Support\Facades\DB;

class ThirdPartyPortalService
{
    public function createProfile(CreateThirdPartyProfileRequest $request, ThirdPartyUser $user): ThirdParties
    {
        if ($user->hasProfile()) {
            throw new ErroredException('User already has a profile.');
        }

        return DB::transaction(function () use ($request, $user) {
            $thirdParty = $this->createThirdParty($request, $user);

            $this->attachTypes($thirdParty, $request, $user);

            $this->linkUserToProfile($user, $thirdParty, $request->requiresApproval());

            return $thirdParty->fresh(['types']);
        });
    }

    protected function createThirdParty(CreateThirdPartyProfileRequest $request, ThirdPartyUser $user): ThirdParties
    {
        $country = $request->getCountry();

        return ThirdParties::create([
            'ThirdPartyName' => $request->validated('Name'),
            'TradingName' => $request->validated('TradingName'),
            'BusinessType' => $request->getBusinessType()?->ID,
            'RegistrationNumber' => $request->validated('RegistrationNumber'),
            'TaxPIN' => $request->validated('TaxPIN'),
            'CountryId' => $country->Id,
            'PhysicalAddress' => $request->validated('PhysicalAddress'),
            'Email' => $request->validated('Email'),
            'Phone' => $request->getFormattedPhone(),
            'Website' => $request->validated('Website'),
            'Status' => $this->getDefaultStatus()->ID,
            'ApprovalStatus' => $request->requiresApproval()
                ? ThirdPartyApprovalStatusEnum::Pending
                : ThirdPartyApprovalStatusEnum::Approved,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);
    }

    protected function attachTypes(ThirdParties $thirdParty, CreateThirdPartyProfileRequest $request, ThirdPartyUser $user): void
    {
        foreach ($request->getTypes() as $type) {
            match ($type) {
                ThirdPartyTypeEnum::Customer => $this->attachCustomerProfile($thirdParty, $request, $user),
                ThirdPartyTypeEnum::Supplier => $this->attachSupplierProfile($thirdParty, $user),
                ThirdPartyTypeEnum::Tenant => $this->attachTenantProfile($thirdParty, $request, $user),
            };
        }
    }

    protected function attachCustomerProfile(ThirdParties $thirdParty, CreateThirdPartyProfileRequest $request, ThirdPartyUser $user): void
    {
        $thirdParty->customerProfile()->create([
            'Gender' => $request->getGender()?->ID,
            'MaritalStatus' => $request->getMaritalStatus()?->ID,
            'Occupation' => $request->getOccupation()?->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        $this->logActivity($thirdParty, $user, 'Customer profile attached');
    }

    protected function attachSupplierProfile(ThirdParties $thirdParty, ThirdPartyUser $user): void
    {
        $supplierId = $this->generateSupplierId();

        $thirdParty->supplierProfile()->create([
            'SupplierID' => $supplierId,
            'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Pending,
            'IsPrequalified' => false,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        $this->logActivity($thirdParty, $user, "Supplier profile attached (ID: {$supplierId}) - pending approval");
    }

    protected function attachTenantProfile(ThirdParties $thirdParty, CreateThirdPartyProfileRequest $request, ThirdPartyUser $user): void
    {
        $thirdParty->tenantProfile()->create([
            'Remarks' => $request->validated('TenantRemarks'),
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        $this->logActivity($thirdParty, $user, 'Tenant profile attached');
    }

    protected function linkUserToProfile(ThirdPartyUser $user, ThirdParties $thirdParty, bool $requiresApproval): void
    {
        $user->update([
            'ThirdPartyId' => $thirdParty->Id,
            'IsActive' => !$requiresApproval,
            'ModifiedBy' => $user->Id,
        ]);

        $this->logActivity($thirdParty, $user, 'User linked to profile');
    }

    protected function generateSupplierId(): string
    {
        $count = DB::table('t_Suppliers')->count();

        do {
            $count++;
            $id = ThirdPartyService::TypeSupplier . str_pad($count, 5, '0', STR_PAD_LEFT);
        } while (DB::table('t_Suppliers')->where('SupplierID', $id)->exists());

        return $id;
    }

    protected function getDefaultStatus(): CodeDetail
    {
        return ThirdPartiesService::codeDetail(ThirdPartyStatusEnum::Active, create: true);
    }

    protected function logActivity(ThirdParties $thirdParty, ThirdPartyUser $user, string $message): void
    {
        activity()
            ->causedBy($user)
            ->performedOn($thirdParty)
            ->event('portal_registration')
            ->log("[Portal] {$message} for {$thirdParty->ThirdPartyName}");
    }

    public function getProfileStatus(ThirdPartyUser $user): array
    {
        if (!$user->hasProfile()) {
            return [
                'has_profile' => false,
                'message' => 'No profile created yet.',
            ];
        }

        $thirdParty = $user->thirdParty;

        return [
            'has_profile' => true,
            'is_approved' => $user->isApproved(),
            'is_supplier' => $user->isSupplier(),
            'is_tenant' => $user->isTenant(),
            'is_customer' => $user->isCustomer(),
            'approval_status' => $thirdParty->ApprovalStatus?->value,
            'message' => $this->getStatusMessage($user),
        ];
    }

    protected function getStatusMessage(ThirdPartyUser $user): string
    {
        if (!$user->IsActive && $user->isSupplier()) {
            return 'Your supplier profile is pending approval.';
        }

        if ($user->isApproved()) {
            return 'Your profile is active.';
        }
        EmailVerificationController

        return 'Your profile is under review.';
    }
}
