<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::select("SELECT u.Email, u.UserID, u.IsActive, u.ThirdPartyId, p.ThirdPartyName
FROM t_ThirdPartyUsers u
JOIN t_ThirdParties p ON u.ThirdPartyId = p.Id
WHERE p.ApprovalStatus = 'A' AND p.Status = 'A' AND (u.IsActive = 0 OR u.IsActive IS NULL)");
if (empty($rows)) {
    echo "No inactive users found for approved+active third parties.\n";
    exit(0);
}
foreach ($rows as $r) {
    echo "Email: {$r->Email} | UserID: {$r->UserID} | IsActive: {$r->IsActive} | ThirdPartyId: {$r->ThirdPartyId} | TP Name: {$r->ThirdPartyName}\n";
}
