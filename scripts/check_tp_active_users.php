<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ThirdParty\ThirdParties;

$tp = ThirdParties::with('users')->where('ApprovalStatus', 'A')->where('Status', 'A')->get();
if ($tp->isEmpty()) {
    echo "No third parties with ApprovalStatus='A' and Status='A' found.\n";
    exit(0);
}
foreach ($tp as $t) {
    echo "ThirdParty Id: {$t->Id} | Name: {$t->ThirdPartyName} | Users: " . count($t->users) . "\n";
    foreach ($t->users as $u) {
        echo " - User Email: {$u->Email} | UserID: {$u->UserID} | IsActive: " . ($u->IsActive ? 'true' : 'false') . "\n";
    }
}
