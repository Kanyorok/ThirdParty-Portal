<?php

namespace App\Services\Procurement\API\Prequalification;

use App\Enums\Procurement\PrequalificationApplicationEnum;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PrequalificationService
{
    public function getOpenRounds(): Collection
    {
        return PrequalificationRound::query()
            ->with(['supplierCategories'])
            ->where('Status', PrequalificationRoundEnum::Open)
            ->whereDate('StartDate', '<=', now())
            ->whereDate('EndDate', '>=', now())
            ->orderBy('EndDate', 'asc')
            ->get();
    }

    public function resolveSupplierContext(): array
    {
        $user = Auth::user();

        if (!$user) {
            return [
                'user' => null,
                'third_party_id' => null,
                'supplier_master' => null,
                'supplier_id' => null,
                'supplier_eligible' => false,
            ];
        }

        $thirdPartyId = $user->third_party_id ?? ($user->thirdParty?->Id);

        $supplierMaster = $thirdPartyId
            ? SupplierMaster::where('ThirdPartyId', $thirdPartyId)->first()
            : null;

        $supplierEligible = $supplierMaster
            ? ($supplierMaster->ApprovalStatus === ThirdPartyApprovalStatusEnum::Approved)
            : false;

        return [
            'user' => $user,
            'third_party_id' => $thirdPartyId,
            'supplier_master' => $supplierMaster,
            'supplier_id' => $supplierMaster?->Id,
            'supplier_eligible' => $supplierEligible,
        ];
    }

    public function initializeApplication(int $roundId): PrequalificationApplication
    {
        $ctx = $this->resolveSupplierContext();

        if (!$ctx['user']) {
            throw new Exception('Unauthenticated.');
        }

        if (!$ctx['supplier_master']) {
            throw new Exception('No supplier profile found.');
        }

        if (!$ctx['supplier_eligible']) {
            throw new Exception('Supplier profile not approved.');
        }

        return PrequalificationApplication::updateOrCreate(
            [
                'RoundID' => $roundId,
                'SupplierID' => $ctx['supplier_id'],
            ],
            [
                'Status' => PrequalificationApplicationEnum::Draft,
                'CreatedBy' => $ctx['user']->Id,
                'ModifiedBy' => $ctx['user']->Id,
            ]
        );
    }

    public function updateApplication(int $applicationId, array $data): PrequalificationApplication
    {
        $user = Auth::user();

        if (!$user) {
            throw new Exception('Unauthenticated.');
        }

        $application = PrequalificationApplication::findOrFail($applicationId);

        return DB::transaction(function () use ($application, $data, $user) {
            $application->update([
                'CategoryID' => $data['CategoryID'] ?? $application->CategoryID,
                'ModifiedBy' => $user->Id,
            ]);

            if (!empty($data['documents']) && method_exists($application, 'documents')) {
                foreach ($data['documents'] as $doc) {
                    $file = $doc['file'] ?? null;
                    $docTypeId = $doc['DocumentTypeID'] ?? null;

                    if (!$file || !$docTypeId) {
                        continue;
                    }

                    $path = $file->store("procurement/applications/{$application->ApplicationID}", 'public');

                    $application->documents()->updateOrCreate(
                        ['DocumentTypeID' => $docTypeId],
                        [
                            'DocumentPath' => $path,
                            'ModifiedBy' => $user->Id,
                        ]
                    );
                }
            }

            return $application->loadMissing(['category', 'documents']);
        });
    }

    public function submitApplication(int $applicationId): PrequalificationApplication
    {
        $user = Auth::user();

        if (!$user) {
            throw new Exception('Unauthenticated.');
        }

        $application = PrequalificationApplication::findOrFail($applicationId);

        if ($application->Status === PrequalificationApplicationEnum::Submitted) {
            throw new Exception('Application already submitted.');
        }

        if (!$application->CategoryID) {
            throw new Exception('Category selection is required.');
        }

        $application->update([
            'Status' => PrequalificationApplicationEnum::Submitted,
            'SubmittedOn' => now(),
            'ModifiedBy' => $user->Id,
        ]);

        return $application;
    }
}
