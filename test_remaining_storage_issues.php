<?php

require __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DMS\DocumentService;
use App\Services\Procurement\EncryptedBidDocumentService;
use App\Models\DMS\Repository;
use App\Models\Auth\User;
use App\Models\Procurement\BidSubmission;
use App\Enums\Core\ExtensionsEnum;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

echo "=== HUNTING REMAINING STORAGE ISSUES ===\n\n";

$user = User::first();
$repository = Repository::where('Name', 'Encrypted Bid Documents')->first();

echo "1. TESTING WITH DIFFERENT FILE TYPES AND SIZES:\n";

$testCases = [
    'small_text' => ['content' => 'Small text', 'extension' => ExtensionsEnum::Txt],
    'large_text' => ['content' => str_repeat('Large content test ', 1000), 'extension' => ExtensionsEnum::Txt],
    'pdf_header' => ['content' => '%PDF-1.4' . "\n" . str_repeat('PDF content ', 100), 'extension' => ExtensionsEnum::Pdf],
    'binary_like' => ['content' => pack('H*', '89504e470d0a1a0a') . str_repeat('binary', 50), 'extension' => ExtensionsEnum::Png],
    'encrypted_content' => ['content' => Crypt::encryptString('Encrypted test content'), 'extension' => ExtensionsEnum::Txt]
];

foreach ($testCases as $testName => $testCase) {
    try {
        echo "   🔄 Testing {$testName} (" . strlen($testCase['content']) . " bytes)...\n";
        
        $result = DocumentService::createContent(
            repository: $repository,
            extension: $testCase['extension'],
            fileName: "test_{$testName}_" . time() . ".{$testCase['extension']->value}",
            content: $testCase['content'],
            actor: $user,
            copyRepoPermissions: true
        );
        
        echo "   ✅ {$testName}: SUCCESS\n";
        
        // Test retrieval
        $retrieved = $result->document->current->getContent();
        if ($retrieved === $testCase['content']) {
            echo "   ✅ {$testName} retrieval: PERFECT\n";
        } else {
            echo "   ⚠️  {$testName} retrieval: MISMATCH\n";
        }
        
    } catch (\Exception $e) {
        echo "   🚨 {$testName} FAILED: " . $e->getMessage() . "\n";
        echo "      Error type: " . get_class($e) . "\n";
        echo "      Location: " . $e->getFile() . ":" . $e->getLine() . "\n";
        
        // This could be our culprit!
        if (strpos($e->getMessage(), 'storage') !== false ||
            strpos($e->getMessage(), 'disk') !== false ||
            strpos($e->getMessage(), 'file') !== false) {
            echo "   🎯 POTENTIAL STORAGE ISSUE FOUND!\n";
        }
    }
}

echo "\n2. TESTING ENCRYPTEDBIDDOCUMENTSERVICE DIRECTLY:\n";

try {
    echo "   🔄 Creating test BidSubmission...\n";
    $bidSubmission = new BidSubmission();
    $bidSubmission->TenderRef = 'TEST-DIRECT-' . time();
    $bidSubmission->SupplierId = 1;
    
    echo "   📄 BidSubmission populated: {$bidSubmission->TenderRef}\n";
    
    // We can't easily test with real UploadedFile, but we can test the key generation
    $reflectionClass = new ReflectionClass(EncryptedBidDocumentService::class);
    $keyMethod = $reflectionClass->getMethod('generateBidEncryptionKey');
    $keyMethod->setAccessible(true);
    
    $encryptionKey = $keyMethod->invoke(null, $bidSubmission);
    echo "   ✅ Encryption key generation: SUCCESS\n";
    echo "   🔑 Key preview: " . substr($encryptionKey, 0, 20) . "...\n";
    
} catch (\Exception $e) {
    echo "   🚨 EncryptedBidDocumentService test FAILED: " . $e->getMessage() . "\n";
}

echo "\n3. CHECKING FOR DISK SPACE AND PERMISSION ISSUES:\n";

try {
    $storagePath = storage_path('app');
    $freeBytes = disk_free_space($storagePath);
    $freeMB = round($freeBytes / 1024 / 1024, 2);
    
    echo "   📊 Free disk space: {$freeMB} MB\n";
    
    if ($freeMB < 100) {
        echo "   ⚠️  LOW DISK SPACE - This could cause storage exceptions!\n";
    } else {
        echo "   ✅ Sufficient disk space available\n";
    }
    
    // Test file permissions
    $testFile = $storagePath . '/permission_test_' . time() . '.txt';
    $written = file_put_contents($testFile, 'permission test');
    
    if ($written) {
        echo "   ✅ File write permissions: OK\n";
        unlink($testFile);
    } else {
        echo "   ❌ File write permissions: FAILED\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Disk/permission check failed: " . $e->getMessage() . "\n";
}

echo "\n4. TESTING STORAGE DISK CONFIGURATION:\n";

try {
    $defaultDisk = config('filesystems.default');
    echo "   📋 Default filesystem disk: {$defaultDisk}\n";
    
    $disks = config('filesystems.disks');
    if (isset($disks[$defaultDisk])) {
        $diskConfig = $disks[$defaultDisk];
        echo "   📋 Disk driver: " . ($diskConfig['driver'] ?? 'unknown') . "\n";
        echo "   📋 Disk root: " . ($diskConfig['root'] ?? 'unknown') . "\n";
        
        $rootPath = $diskConfig['root'] ?? '';
        if (!empty($rootPath) && !file_exists($rootPath)) {
            echo "   ❌ Disk root path does not exist!\n";
        } elseif (!empty($rootPath) && !is_writable($rootPath)) {
            echo "   ❌ Disk root path is not writable!\n";
        } else {
            echo "   ✅ Disk configuration appears valid\n";
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ Storage configuration check failed: " . $e->getMessage() . "\n";
}

echo "\n5. CHECKING FOR TRANSACTION/ROLLBACK ISSUES:\n";

try {
    echo "   🔄 Testing database transaction with storage...\n";
    
    DB::beginTransaction();
    
    $result = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Txt,
        fileName: "transaction_test_" . time() . ".txt",
        content: "Transaction test content",
        actor: $user,
        copyRepoPermissions: false
    );
    
    DB::commit();
    echo "   ✅ Transaction + storage: SUCCESS\n";
    
} catch (\Exception $e) {
    DB::rollBack();
    echo "   🚨 Transaction + storage FAILED: " . $e->getMessage() . "\n";
    
    if (strpos($e->getMessage(), 'transaction') !== false) {
        echo "   🎯 TRANSACTION-RELATED STORAGE ISSUE!\n";
    }
}

echo "\n=== REMAINING STORAGE ISSUES HUNT COMPLETE ===\n\n";

echo "💡 IF ALL TESTS PASS:\n";
echo "   The storage exception might be happening only under specific conditions:\n";
echo "   - Large file uploads from portal\n";
echo "   - Concurrent requests\n";
echo "   - Specific file types/mime types\n";
echo "   - Authentication context differences\n\n";

echo "🔍 NEXT STEPS:\n";
echo "   1. Check Laravel logs during actual portal uploads\n";
echo "   2. Monitor network/server resources during uploads\n";
echo "   3. Test with actual portal file upload (not just API calls)\n";

