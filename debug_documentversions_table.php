<?php

require __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\DMS\DocumentVersion;

echo "=== t_DocumentVersions TABLE VERIFICATION ===\n\n";

echo "1. CHECKING t_DocumentVersions TABLE EXISTENCE:\n";

try {
    if (Schema::hasTable('t_DocumentVersions')) {
        $count = DB::table('t_DocumentVersions')->count();
        echo "   ✅ t_DocumentVersions: EXISTS (Records: {$count})\n";

        // Check table structure
        echo "\n2. CHECKING TABLE STRUCTURE:\n";
        $columns = DB::select("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
                              FROM INFORMATION_SCHEMA.COLUMNS
                              WHERE TABLE_NAME = 't_DocumentVersions'
                              ORDER BY ORDINAL_POSITION");

        foreach ($columns as $column) {
            $nullable = $column->IS_NULLABLE === 'YES' ? 'NULL' : 'NOT NULL';
            echo "   📋 {$column->COLUMN_NAME}: {$column->DATA_TYPE} ({$nullable})\n";
        }

        // Check if Blob column exists
        $blobColumn = collect($columns)->firstWhere('COLUMN_NAME', 'Blob');
        if ($blobColumn) {
            echo "   ✅ 'Blob' column exists: {$blobColumn->DATA_TYPE}\n";
        } else {
            echo "   ❌ 'Blob' column MISSING!\n";
        }

    } else {
        echo "   ❌ t_DocumentVersions: MISSING TABLE!\n";
        echo "   🚨 This is why DocumentService fails!\n";
        return;
    }
} catch (\Exception $e) {
    echo "   ❌ Table check error: " . $e->getMessage() . "\n";
    return;
}

echo "\n3. CHECKING EXISTING DOCUMENT VERSIONS:\n";
try {
    $versions = DocumentVersion::limit(3)->get(['Id', 'Name', 'Version', 'Path', 'Size', 'Blob']);

    if ($versions->count() > 0) {
        echo "   ✅ Found {$versions->count()} document versions:\n";
        foreach ($versions as $version) {
            $blobSize = $version->Blob ? strlen($version->Blob) : 0;
            echo "      - ID {$version->Id}: {$version->Name} (v{$version->Version})\n";
            echo "        Size: {$version->Size} bytes, Blob: {$blobSize} bytes\n";
            echo "        Path: {$version->Path}\n";
        }
    } else {
        echo "   ⚠️  No document versions found\n";
    }
} catch (\Exception $e) {
    echo "   ❌ DocumentVersion model error: " . $e->getMessage() . "\n";
}

echo "\n4. TESTING DocumentVersion::getContent() METHOD:\n";
try {
    $version = DocumentVersion::first();
    if ($version) {
        echo "   📋 Testing with version ID: {$version->Id}\n";

        if (method_exists($version, 'getContent')) {
            $content = $version->getContent();
            echo "   ✅ getContent() method exists and returned: " . strlen($content) . " bytes\n";
        } else {
            echo "   ❌ getContent() method MISSING!\n";
            echo "   🎯 THIS IS THE ROOT CAUSE OF 'Saving file failed'\n";
        }
    } else {
        echo "   ⚠️  No document versions to test\n";
    }
} catch (\Exception $e) {
    echo "   ❌ getContent() test error: " . $e->getMessage() . "\n";
}

echo "\n5. CHECKING WHAT CONTENT IS ACTUALLY STORED:\n";
try {
    $versionsWithBlob = DocumentVersion::whereNotNull('Blob')->limit(2)->get(['Id', 'Name', 'Blob']);

    if ($versionsWithBlob->count() > 0) {
        echo "   ✅ Found versions with Blob content:\n";
        foreach ($versionsWithBlob as $version) {
            $blobPreview = substr($version->Blob, 0, 50);
            echo "      - ID {$version->Id}: {$version->Name}\n";
            echo "        Blob preview: {$blobPreview}...\n";
            echo "        Total size: " . strlen($version->Blob) . " bytes\n";
        }
    } else {
        echo "   ⚠️  No versions found with Blob content\n";
        echo "   🔍 Content might be stored in Path (file system) instead\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Blob content check error: " . $e->getMessage() . "\n";
}

echo "\n=== t_DocumentVersions VERIFICATION COMPLETE ===\n";
