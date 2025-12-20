<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

try {
    // Try to insert a user without ThirdPartyId
    $id = DB::table('t_ThirdPartyUsers')->insertGetId([
        'FirstName' => 'Debug',
        'LastName' => 'NullTest',
        'Email' => 'debugnull@test.com',
        'Phone' => '0000',
        'Password' => 'test',
        'IsActive' => 0,
        'CreatedBy' => 1,
        'ModifiedBy' => 1,
        'UserID' => 'DEBUG' . rand(100, 999),
        'Gender' => 153, // Valid ID
        'ModifiedOn' => now(), // Required?
        'CreatedOn' => now()
    ]);
    echo "Success! Inserted User ID: $id without ThirdPartyId.\n";
    // Cleanup
    DB::table('t_ThirdPartyUsers')->where('Id', $id)->delete();
} catch (\Exception $e) {
    echo "Failed: " . $e->getMessage() . "\n";
}
