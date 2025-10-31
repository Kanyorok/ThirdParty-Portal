<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ThirdParty\ThirdPartyUser;

$email = $argv[1] ?? 'jd@craftsilicon.com';
$user = ThirdPartyUser::with('thirdParty')->where('Email', $email)->first();
if (!$user) {
    echo "User not found: $email\n";
    exit(0);
}
$tp = $user->thirdParty;
$approval = 'null';
if ($tp) {
    $a = $tp->ApprovalStatus;
    if (is_object($a)) {
        $approval = $a->value ?? ($a->name ?? (string)$a);
    } else {
        $approval = $a ?? 'null';
    }
}
echo "Email: " . $user->Email . "\n";
echo "UserID: " . $user->UserID . "\n";
echo "IsActive: " . ($user->IsActive ? 'true' : 'false') . "\n";
echo "ThirdPartyId: " . ($user->ThirdPartyId ?? 'null') . "\n";
echo "ThirdParty ApprovalStatus: " . $approval . "\n";
echo "ThirdParty Name: " . ($tp->ThirdPartyName ?? 'null') . "\n";
// show password hash presence (not the plain password)
echo "Password hash length: " . (isset($user->Password) ? strlen($user->Password) : 0) . "\n";
// indicate whether isApproved() would return true
$approved = $user->isApproved() ? 'true' : 'false';
echo "isApproved(): $approved\n";
