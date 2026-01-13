<?php

namespace App\Services\Insurance;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Currency;
use App\Models\Insurance\BancassuranceClaim;
use App\Models\Insurance\BancassurancePolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

class BancassuranceClaimService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public BancassuranceClaim $bancassuranceClaim)
    {
        //
    }

    public static function create(
        BancassurancePolicy $PolicyId,
        CodeDetail          $ClaimType,
        string              $ClaimReason,
        float               $ClaimAmount,
        Currency            $Currency,
        Carbon              $ClaimDate,
        CodeDetail          $Status,
        User                $user,
        UploadedFile        $document = null
    ): self
    {
        $claim = BancassuranceClaim::create([
            'PolicyId' => $PolicyId->Id,
            'ClaimType' => $ClaimType->ID,
            'ClaimReason' => $ClaimReason,
            'ClaimAmount' => $ClaimAmount,
            'CurrencyId' => $Currency->Id,
            'ClaimDate' => $ClaimDate,
            'Status' => $Status->ID,
            'CreatedBy' => $user->Id,
            'ModifiedBy' => $user->Id,
        ]);

        if ($document) {
            $claim->newDocument(
                ModulesEnum::Insurance,
                $document,
                [PermissionEnum::BancassuranceClaimView->value],
                $user
            );
        }

        activity()->causedBy($user->Id)->performedOn($claim)->event('create')->log("Added Policy claim {$claim->Id}.");
        return new self($claim);
    }
}
