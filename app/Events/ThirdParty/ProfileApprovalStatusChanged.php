<?php

namespace App\Events\ThirdParty;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Models\ThirdParty\SupplierMaster;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProfileApprovalStatusChanged
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public SupplierMaster $supplier,
        public ThirdPartyApprovalStatusEnum $oldStatus,
        public ThirdPartyApprovalStatusEnum $newStatus,
        public ?int $approvedBy = null
    ) {
    }
}
