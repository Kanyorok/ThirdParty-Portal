<?php

use App\Models\ThirdParty\ThirdParties;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

try {
    echo "Attempting to create ThirdParty with minimal fields...\n";

    // Simulate what RegistrationService does in Step 1
    // It passes null for many fields
    $party = ThirdParties::create([
        'ThirdPartyName' => 'Debug Test Party',
        'TradingName' => 'Debug Test Party',
        'BusinessType' => null, // Suspect
        'RegistrationNumber' => null, // Suspect
        'TaxPIN' => null,
        'VATNumber' => null,
        'CountryId' => 1, // Providing 1 for Country ID just in case
        'LocationId' => null,
        'PhysicalAddress' => null,
        'Email' => 'debug@test.com',
        'Phone' => '1234567890',
        'Website' => null,
        'Status' => null,
        'Extra' => null,
        'CreatedBy' => 1,
        'ModifiedBy' => 1,
    ]);

    echo "Success! Created Party ID: " . $party->Id . "\n";
    // Clean up
    $party->forceDelete();
} catch (\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . "\n";
    if ($e instanceof \Illuminate\Database\QueryException) {
        echo "SQL: " . $e->getSql() . "\n";
    }
}
