<?php

use App\Models\ThirdParty\SupplierMaster;
use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use Illuminate\Support\Facades\DB;

// Load Composer's autoloader if running standalone, but here we run via tinker or artisan
// php artisan tinker migrate_status.php

echo "Starting migration of Supplier Approval Status...\n";

$suppliers = SupplierMaster::where('ApprovalStatus', 'P') // Pending
    ->orWhere('ApprovalStatus', '!=', 'U') // Or anything not U, A, R? No, strictly P is safer.
    ->get();

$count = 0;
foreach ($suppliers as $supplier) {
    // Check if it has workflow history (meaning it was submitted)
    if ($supplier->workflowHistory()->exists()) {
        try {
            $supplier->ApprovalStatus = ThirdPartyApprovalStatusEnum::Submitted;
            $supplier->save();
            echo "Updated Supplier {$supplier->SupplierID} to Submitted ('U').\n";
            $count++;
        } catch (\Exception $e) {
            echo "Failed to update Supplier {$supplier->SupplierID}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Migration complete. Updated {$count} suppliers.\n";
