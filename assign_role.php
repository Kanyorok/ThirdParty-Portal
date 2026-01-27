<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\Auth\User::where('UserID', 'CSADM')->first();
$branch = App\Models\Core\Branch::where('Name', 'Head Office')->first();

// Find an admin role
$adminRole = App\Models\Auth\Role::where('name', 'like', '%admin%')->first();
if (!$adminRole) {
    $adminRole = App\Models\Auth\Role::first();
}

echo "User: {$user->UserID} (ID: {$user->Id})\n";
echo "Branch: {$branch->Name} (ID: {$branch->Id})\n";
echo "Role: " . ($adminRole ? $adminRole->name : 'NOT FOUND') . "\n\n";

if ($user && $branch && $adminRole) {
    // Check if already exists
    $existing = App\Models\Auth\ModelRole::where('model_id', $user->Id)
        ->where('model_type', 'User')
        ->where('BranchId', $branch->Id)
        ->first();
    
    if ($existing) {
        echo "Role already exists!\n";
    } else {
        $modelRole = App\Models\Auth\ModelRole::create([
            'role_id' => $adminRole->id,
            'model_type' => 'User',
            'model_id' => $user->Id,
            'BranchId' => $branch->Id,
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);
        
        echo "✓ SUCCESS: Assigned '{$adminRole->name}' role to {$user->UserID} for {$branch->Name}\n";
    }
} else {
    echo "ERROR: Missing required data\n";
}
