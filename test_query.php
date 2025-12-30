<?php

use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$search = '';
$excludeType = 'TN';

echo "Testing Search for Type Exclusion: $excludeType\n";

// Find ANY party that is NOT a Tenant
$eligibleParams = [
    'TN' => 'Tenant',
    'SU' => 'Supplier',
    'CU' => 'Customer'
];

foreach ($eligibleParams as $code => $desc) {
    echo "Checking for parties eligible to be '$desc' (Not '$code')...\n";
    $count = ThirdParties::whereDoesntHave('types', function ($q) use ($code) {
        $q->where('Code', $code);
    })->count();

    echo "Found $count parties eligible to be $desc.\n";

    if ($count > 0) {
        $sample = ThirdParties::whereDoesntHave('types', function ($q) use ($code) {
            $q->where('Code', $code);
        })->first();
        echo "Sample: {$sample->ThirdPartyName} (ID: {$sample->Id})\n";
    }
    echo "------------------------------------------------\n";
}
