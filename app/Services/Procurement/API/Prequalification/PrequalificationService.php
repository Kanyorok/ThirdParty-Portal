<?php

namespace App\Services\Procurement\API\Prequalification;

use App\Models\Procurement\Prequalification\PrequalificationRound;
use App\Models\Procurement\Prequalification\PrequalificationApplication;
use App\Enums\Procurement\PrequalificationRoundEnum;
use App\Enums\Procurement\PrequalificationApplicationEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;

class PrequalificationService
{
    public function getOpenRounds(): Collection
    {
        return PrequalificationRound::with(['supplierCategories'])
            ->where('Status', PrequalificationRoundEnum::Open)
            ->whereDate('StartDate', '<=', now())
            ->whereDate('EndDate', '>=', now())
            ->orderBy('EndDate', 'asc')
            ->get();
    }

    public function initializeApplication(int $roundId)
    {
        $user = Auth::user();
        
        if (!$user->thirdParty || !$user->thirdParty->supplierMaster) {
            throw new Exception("Supplier profile not found or not approved.");
        }

        $supplierId = $user->thirdParty->supplierMaster->SupplierID 
                   ?? $user->thirdParty->supplierMaster->id;

        return PrequalificationApplication::updateOrCreate(
            [
                'RoundID' => $roundId,
                'SupplierID' => $supplierId,
            ],
            [
                'Status' => PrequalificationApplicationEnum::Draft,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'updated_at' => now()
            ]
        );
    }

    public function updateApplication(int $applicationId, array $data): PrequalificationApplication
    {
        $application = PrequalificationApplication::findOrFail($applicationId);

        return DB::transaction(function () use ($application, $data) {
            $application->update([
                'CategoryID' => $data['CategoryID'] ?? $application->CategoryID,
                'ModifiedBy' => Auth::id(),
            ]);

            if (isset($data['documents'])) {
                foreach ($data['documents'] as $doc) {
                    $file = $doc['file'];
                    $path = $file->store("procurement/applications/{$application->ApplicationID}", 'public');

                    $application->documents()->updateOrCreate(
                        ['DocumentTypeID' => $doc['DocumentTypeID']],
                        [
                            'DocumentPath' => $path,
                            'ModifiedBy'   => Auth::id()
                        ]
                    );
                }
            }

            return $application->load(['category', 'documents']);
        });
    }

    public function submitApplication(int $applicationId): PrequalificationApplication
    {
        $application = PrequalificationApplication::findOrFail($applicationId);

        if ($application->Status === PrequalificationApplicationEnum::Submitted) {
            throw new Exception("Application already submitted.");
        }

        if (!$application->CategoryID) {
            throw new Exception("Category selection is required.");
        }

        $application->update([
            'Status'      => PrequalificationApplicationEnum::Submitted,
            'SubmittedOn' => now(),
            'ModifiedBy'  => Auth::id(),
        ]);

        return $application;
    }
}