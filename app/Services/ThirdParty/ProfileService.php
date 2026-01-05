<?php

namespace App\Services\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyTypeEnum;
use App\Events\ThirdParty\ProfileCreated;
use App\Events\ThirdParty\ProfileUpdated;
use App\Exceptions\ThirdParty\DuplicateProfileException;
use App\Exceptions\ThirdParty\ProfileCreationException;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\SupplierMaster;
use App\Models\ThirdParty\ThirdParties;
use App\Models\ThirdParty\ThirdPartyType;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Repositories\ThirdParty\Contracts\SupplierRepositoryInterface;
use App\Repositories\ThirdParty\Contracts\ThirdPartyRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProfileService
{
    public function __construct(
        protected ThirdPartyRepositoryInterface $thirdPartyRepository,
        protected SupplierRepositoryInterface $supplierRepository
    ) {}

    /**
     * Create a supplier profile for the user
     *
     * @throws ProfileCreationException
     * @throws DuplicateProfileException
     */
    public function createSupplierProfile(ThirdPartyUser $user, array $data): ThirdParties
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                // Check for duplicate supplier profile
                $this->checkDuplicateProfile($user, ThirdPartyTypeEnum::Supplier);

                // Create or update ThirdParty entity
                $thirdParty = $this->createOrUpdateThirdParty($user, $data);

                // Create SupplierMaster record
                $supplierMaster = $this->supplierRepository->create([
                    'ThirdPartyId' => $thirdParty->Id,
                    'ApprovalStatus' => ThirdPartyApprovalStatusEnum::Pending,
                    'IsPrequalified' => false,
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);

                // Link to ThirdParty via pivot table
                $this->linkProfileType($thirdParty, ThirdPartyTypeEnum::Supplier, $supplierMaster->Id, $user);

                // Attach supplier categories if provided
                if (!empty($data['supplier_categories'])) {
                    $this->supplierRepository->attachCategories($supplierMaster, $data['supplier_categories']);
                }

                // Update user's ThirdPartyId if not set
                $this->linkUserToThirdParty($user, $thirdParty);

                // Clear cache
                $this->clearProfileCache($user);

                // Fire event
                event(new ProfileCreated($thirdParty->Id, $user->Id, ThirdPartyTypeEnum::Supplier));

                return $thirdParty->fresh(['types', 'supplierMaster']);
            });
        } catch (DuplicateProfileException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create supplier profile', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ProfileCreationException(
                'Failed to create supplier profile',
                500,
                $e
            );
        }
    }

    /**
     * Create a tenant profile for the user
     *
     * @throws ProfileCreationException
     * @throws DuplicateProfileException
     */
    public function createTenantProfile(ThirdPartyUser $user, array $data): ThirdParties
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                // Check for duplicate tenant profile
                $this->checkDuplicateProfile($user, ThirdPartyTypeEnum::Tenant);

                // Create or update ThirdParty entity
                $thirdParty = $this->createOrUpdateThirdParty($user, $data);

                // Create PropertyNewTenant record
                $tenant = PropertyNewTenant::create([
                    'ThirdPartyId' => $thirdParty->Id,
                    'TenantType' => $data['tenant_type'] ?? null,
                    'Remarks' => $data['remarks'] ?? null,
                    'IsActive' => true,
                    'CreatedBy' => $user->Id,
                    'ModifiedBy' => $user->Id,
                ]);

                // Link to ThirdParty via pivot table
                $this->linkProfileType($thirdParty, ThirdPartyTypeEnum::Tenant, $tenant->Id, $user);

                // Update user's ThirdPartyId if not set
                $this->linkUserToThirdParty($user, $thirdParty);

                // Clear cache
                $this->clearProfileCache($user);

                // Fire event
                event(new ProfileCreated($thirdParty->Id, $user->Id, ThirdPartyTypeEnum::Tenant));

                return $thirdParty->fresh(['types', 'tenants']);
            });
        } catch (DuplicateProfileException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create tenant profile', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ProfileCreationException(
                'Failed to create tenant profile',
                500,
                $e
            );
        }
    }

    /**
     * Create a customer profile for the user
     *
     * @throws ProfileCreationException
     * @throws DuplicateProfileException
     */
    public function createCustomerProfile(ThirdPartyUser $user, array $data): ThirdParties
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                // Check for duplicate customer profile
                $this->checkDuplicateProfile($user, ThirdPartyTypeEnum::Customer);

                // Create or update ThirdParty entity
                $thirdParty = $this->createOrUpdateThirdParty($user, $data);

                // Link customer type to ThirdParty via pivot table
                $this->linkProfileType($thirdParty, ThirdPartyTypeEnum::Customer, $thirdParty->Id, $user);

                // Update user's ThirdPartyId if not set
                $this->linkUserToThirdParty($user, $thirdParty);

                // Clear cache
                $this->clearProfileCache($user);

                // Fire event
                event(new ProfileCreated($thirdParty->Id, $user->Id, ThirdPartyTypeEnum::Customer));

                return $thirdParty->fresh(['types']);
            });
        } catch (DuplicateProfileException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create customer profile', [
                'user_id' => $user->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new ProfileCreationException(
                'Failed to create customer profile',
                500,
                $e
            );
        }
    }

    /**
     * Get all profiles for a user (with caching)
     */
    public function getUserProfiles(ThirdPartyUser $user): ?ThirdParties
    {
        return $this->thirdPartyRepository->getUserProfiles($user);
    }

    /**
     * Update profile information
     *
     * @throws ProfileCreationException
     */
    public function updateProfile(ThirdPartyUser $user, ThirdParties $thirdParty, array $data): ThirdParties
    {
        try {
            return DB::transaction(function () use ($user, $thirdParty, $data) {
                $changes = array_diff_assoc(
                    $this->mapThirdPartyData($data, $user),
                    $thirdParty->only(array_keys($this->mapThirdPartyData($data, $user)))
                );

                if (empty($changes)) {
                    return $thirdParty;
                }

                $updatedThirdParty = $this->thirdPartyRepository->update($thirdParty, $changes);

                // Clear cache
                $this->clearProfileCache($user);

                // Fire event
                event(new ProfileUpdated($updatedThirdParty, $user, $changes));

                return $updatedThirdParty;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update profile', [
                'user_id' => $user->Id,
                'third_party_id' => $thirdParty->Id,
                'error' => $e->getMessage(),
            ]);

            throw new ProfileCreationException(
                'Failed to update profile',
                500,
                $e
            );
        }
    }

    /**
     * Check for duplicate profile type
     *
     * @throws DuplicateProfileException
     */
    protected function checkDuplicateProfile(ThirdPartyUser $user, ThirdPartyTypeEnum $profileType): void
    {
        if (!$user->hasProfile()) {
            return;
        }

        $thirdParty = $this->thirdPartyRepository->findByUserId($user->Id);
        if (!$thirdParty) {
            return;
        }

        if ($this->thirdPartyRepository->hasProfileType($thirdParty, $profileType)) {
            throw new DuplicateProfileException($profileType->label());
        }
    }

    /**
     * Create or update ThirdParty entity
     */
    protected function createOrUpdateThirdParty(ThirdPartyUser $user, array $data): ThirdParties
    {
        if ($user->ThirdPartyId) {
            $thirdParty = $this->thirdPartyRepository->findById($user->ThirdPartyId);
            if ($thirdParty) {
                return $this->thirdPartyRepository->update(
                    $thirdParty,
                    $this->mapThirdPartyData($data, $user)
                );
            }
        }

        return $this->thirdPartyRepository->create($this->mapThirdPartyData($data, $user));
    }

    /**
     * Map request data to ThirdParty model attributes
     */
    protected function mapThirdPartyData(array $data, ThirdPartyUser $user): array
    {
        return [
            'ThirdPartyName' => $data['third_party_name'],
            'TradingName' => $data['trading_name'] ?? null,
            'BusinessType' => $data['business_type'] ?? null,
            'RegistrationNumber' => $data['registration_number'] ?? null,
            'TaxPIN' => $data['tax_pin'] ?? null,
            'CountryId' => $data['country_id'] ?? null,
            'LocationId' => $data['location_id'] ?? null,
            'PhysicalAddress' => $data['physical_address'] ?? null,
            'Email' => $data['email'] ?? $user->Email,
            'Phone' => $data['phone'] ?? $user->Phone,
            'Website' => $data['website'] ?? null,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ];
    }

    /**
     * Link profile type to ThirdParty via pivot (refactored to use enum)
     */
    protected function linkProfileType(
        ThirdParties $thirdParty,
        ThirdPartyTypeEnum $profileType,
        int $ThirdPartyId,
        ThirdPartyUser $user
    ): void {
        $this->thirdPartyRepository->attachProfileType($thirdParty, $profileType, $ThirdPartyId, $user->Id);
    }

    /**
     * Link user to ThirdParty if not already linked
     */
    protected function linkUserToThirdParty(ThirdPartyUser $user, ThirdParties $thirdParty): void
    {
        if (!$user->ThirdPartyId) {
            $user->update(['ThirdPartyId' => $thirdParty->Id]);
        }
    }

    /**
     * Clear profile-related cache
     */
    protected function clearProfileCache(ThirdPartyUser $user): void
    {
        Cache::forget("user_profile_{$user->Id}");
        Cache::forget("third_party_{$user->ThirdPartyId}");
        Cache::tags(['third_party', 'profiles'])->flush();
    }
}
