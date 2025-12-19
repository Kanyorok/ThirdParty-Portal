// Auto-submit script for Pending Suppliers
$suppliers = \App\Models\ThirdParty\SupplierMaster::where('ApprovalStatus', \App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum::Pending)->get();
$service = app(\App\Services\ThirdParties\SupplierWorkflowService::class);
$admin = \App\Models\Auth\User::first(); // Assuming first user is admin/system

foreach ($suppliers as $supplier) {
if (!$supplier->workflowHistory()->exists()) {
try {
echo "Submitting Supplier: " . $supplier->SupplierID . "\n";
$service->submit($supplier, $admin);
} catch (\Exception $e) {
echo "Error submitting " . $supplier->SupplierID . ": " . $e->getMessage() . "\n";
}
} else {
echo "Supplier " . $supplier->SupplierID . " already has workflow history.\n";
}
}
echo "Done.\n";