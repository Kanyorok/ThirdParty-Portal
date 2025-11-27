<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ThirdParty\ThirdPartyUser;

$users = ThirdPartyUser::with('thirdParty')->take(20)->get();
foreach ($users as $u) {
    $tp = $u->thirdParty;
    $approval = 'null';
    if ($tp) {
        $a = $tp->ApprovalStatus;
        if (is_object($a)) {
            $approval = $a->value ?? ($a->name ?? (string)$a);
        } else {
            $approval = $a ?? 'null';
        }
    }
    echo 'Email:' . ($u->Email ?? '') . ' | UserID:' . ($u->UserID ?? '') . ' | IsActive:' . ($u->IsActive ? 'true' : 'false') . ' | ThirdPartyId:' . ($u->ThirdPartyId ?? 'null') . ' | TP Approval:' . $approval . "\n";
}
