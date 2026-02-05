<?php

use App\Models\Finance\FinanceInvoice;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\DB;

// Get distinct customer IDs from Invoices
$customerIds = FinanceInvoice::distinct()->pluck('CustomerID')->take(100);

echo "Found " . $customerIds->count() . " customers with invoices.\n";

if ($customerIds->isEmpty()) {
    echo "No invoices found. Checking distinct types on all ThirdParties...\n";
    $types = DB::table('t_ThirdPartyType_ThirdParties')
        ->select('PartyType')
        ->distinct()
        ->get();
    print_r($types);
    exit;
}

// Analyze types for these customers
$typesCount = [];

foreach ($customerIds as $id) {
    $customer = ThirdParties::with('types')->find($id);
    if (!$customer) continue;

    foreach ($customer->types as $type) {
        $pivotType = $type->pivot->PartyType;
        // Also get the Type Code/Description if available
        $code = $type->Code ?? 'N/A';
        $desc = $type->Description ?? 'N/A';
        
        $key = "Pivot: $pivotType | Code: $code | Desc: $desc";
        
        if (!isset($typesCount[$key])) {
            $typesCount[$key] = 0;
        }
        $typesCount[$key]++;
    }
}

echo "\nType Distribution among Invoice Customers:\n";
print_r($typesCount);

// Also check what types Suppliers have, to see what to EXCLUDE
echo "\nChecking Supplier Types (sample):\n";
$supplierTypes = DB::table('t_ThirdPartyType_ThirdParties')
    ->where('PartyType', 'like', '%Supplier%')
    ->distinct()
    ->get(['PartyType']);

print_r($supplierTypes);
