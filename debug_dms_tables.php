<?php

require __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DMS\Repository;
use App\Models\DMS\Document;
use App\Models\Auth\User;

echo "=== DMS DATABASE TABLES DEBUG ===\n\n";

// Step 1: Check if DMS tables exist
echo "1. CHECKING DMS TABLE EXISTENCE:\n";

$tables = ['t_Repositories', 't_Documents', 't_DocumentContent'];
foreach ($tables as $table) {
    try {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "   ✅ {$table}: EXISTS (Records: {$count})\n";
        } else {
            echo "   ❌ {$table}: MISSING\n";
        }
    } catch (\Exception $e) {
        echo "   ❌ {$table}: ERROR - {$e->getMessage()}\n";
    }
}

echo "\n2. CHECKING REPOSITORY MODEL ACCESS:\n";
try {
    $repoCount = Repository::count();
    echo "   ✅ Repository model working: {$repoCount} repositories found\n";

    $repositories = Repository::limit(3)->get(['Name', 'Description', 'Visibility']);
    foreach ($repositories as $repo) {
        $visibility = is_object($repo->Visibility) ? $repo->Visibility->value : $repo->Visibility;
        echo "      - {$repo->Name} (Visibility: {$visibility})\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Repository model error: " . $e->getMessage() . "\n";
}

echo "\n3. CHECKING 'ENCRYPTED BID DOCUMENTS' REPOSITORY:\n";
try {
    $bidRepo = Repository::where('Name', 'Encrypted Bid Documents')->first();
    if ($bidRepo) {
        echo "   ✅ 'Encrypted Bid Documents' repository exists:\n";
        echo "      - ID: {$bidRepo->Id}\n";
        echo "      - RepositoryId: {$bidRepo->RepositoryId}\n";
        $visibility = is_object($bidRepo->Visibility) ? $bidRepo->Visibility->value : $bidRepo->Visibility;
        echo "      - Visibility: {$visibility}\n";
        echo "      - CreatedBy: {$bidRepo->CreatedBy}\n";
    } else {
        echo "   ⚠️  'Encrypted Bid Documents' repository NOT found\n";
        echo "      This will be created automatically on first bid submission\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Repository check error: " . $e->getMessage() . "\n";
}

echo "\n4. CHECKING USER AVAILABILITY FOR REPOSITORY CREATION:\n";
try {
    $user1 = User::find(1);
    if ($user1) {
        echo "   ✅ User ID 1 exists: {$user1->FullName} (Active: " . ($user1->DeletedOn ? "NO" : "YES") . ")\n";
    } else {
        echo "   ⚠️  User ID 1 not found\n";
    }

    $firstActiveUser = User::whereNull('DeletedOn')->first();
    if ($firstActiveUser) {
        echo "   ✅ First active user ID {$firstActiveUser->Id}: {$firstActiveUser->FullName}\n";
    } else {
        echo "   ❌ No active users found!\n";
    }
} catch (\Exception $e) {
    echo "   ❌ User check error: " . $e->getMessage() . "\n";
}

echo "\n5. CHECKING DOCUMENT MODEL ACCESS:\n";
try {
    $docCount = Document::count();
    echo "   ✅ Document model working: {$docCount} documents found\n";
} catch (\Exception $e) {
    echo "   ❌ Document model error: " . $e->getMessage() . "\n";
}

echo "\n=== DMS DATABASE DEBUG COMPLETE ===\n";
