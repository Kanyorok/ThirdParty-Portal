<?php

use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\ThirdParty\ThirdPartyResource;

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'robertmbugua.kanyoro@gmail.com';
$password = 'password'; // Assuming this is the password being tried? User reset it...
// Wait, user reset it. I don't know the new password.
// But I can check if finding the user throws an error.

echo "Login Debug Start for $email\n";

try {
    $user = ThirdPartyUser::where('Email', $email)->first();
    if (!$user) {
        throw new Exception("User not found via Eloquent.");
    }
    echo "User found: " . $user->Id . "\n";

    // Skip password check for debug (or I can set it to a known one temporarily if needed)
    // But let's check validation logic first.

    if (!$user->IsActive) {
        echo "User is Inactive.\n";
    }

    $thirdParty = ThirdParties::find($user->ThirdPartyId);
    if (!$thirdParty) {
        if (!$user->isApproved()) {
            echo "User unlinked and not approved.\n";
        }
    } else {
        echo "ThirdParty Found: " . $thirdParty->Id . "\n";
        if (!$thirdParty->isApproved()) {
            echo "ThirdParty not approved.\n";
        } else {
            echo "ThirdParty Approved.\n";
        }
    }

    // Simulate attribute setting
    $thirdParty->setAttribute('FirstName', $user->FirstName);
    $thirdParty->setAttribute('LastName', $user->LastName);

    // Simulate loading relations
    // This is where it might fail if ThirdPartyTypeEnum is used in a relation
    $thirdParty->load(['types', 'country', 'categories']);
    echo "Relations loaded.\n";

    // Resource creation
    $resource = (new ThirdPartyResource($thirdParty))->resolve();
    echo "Resource resolved.\n";
} catch (\Exception $e) {
    echo "EXCEPTION CAUGHT:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
