<?php

require __DIR__ . '/vendor/autoload.php';

// Initialize Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DMS\DocumentService;
use App\Models\DMS\Repository;
use App\Models\Auth\User;
use App\Enums\Core\ExtensionsEnum;

echo "=== NEW DOCUMENTSERVICE STORAGE EXCEPTION TEST ===\n\n";

// Get objects for testing
$user = User::first();
$repository = Repository::where('Name', 'Encrypted Bid Documents')->first();

echo "1. TESTING CURRENT DOCUMENTSERVICE STORAGE:\n";
echo "   📤 User: {$user->FullName} (ID: {$user->Id})\n";
echo "   📂 Repository: {$repository->Name} (ID: {$repository->Id})\n";

$testContent = "NEW STORAGE EXCEPTION TEST - " . now();
$fileName = "storage_exception_test_" . time() . ".txt";

echo "\n2. ATTEMPTING DOCUMENTSERVICE::CREATECONTENT():\n";
echo "   📄 Content: {$testContent}\n";
echo "   📁 Filename: {$fileName}\n";

try {
    echo "   🔄 Calling DocumentService::createContent()...\n";

    $result = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Txt,
        fileName: $fileName,
        content: $testContent,
        actor: $user,
        copyRepoPermissions: false
    );

    echo "   ✅ DocumentService::createContent() SUCCESS!\n";
    echo "      - Document ID: {$result->document->DocumentId}\n";

    // Now test content retrieval
    echo "\n3. TESTING CONTENT RETRIEVAL:\n";
    $retrievedContent = $result->document->current->getContent();

    if ($retrievedContent === $testContent) {
        echo "   ✅ Content retrieval SUCCESS! Perfect match.\n";
    } else {
        echo "   ❌ Content mismatch!\n";
        echo "      Expected: '{$testContent}'\n";
        echo "      Retrieved: '{$retrievedContent}'\n";
    }

} catch (\Exception $e) {
    echo "   🚨 STORAGE EXCEPTION CAUGHT!\n";
    echo "   📍 Exception type: " . get_class($e) . "\n";
    echo "   💥 Error message: " . $e->getMessage() . "\n";
    echo "   📋 File: " . $e->getFile() . ":" . $e->getLine() . "\n";

    // Get the full stack trace
    echo "\n   🔍 STACK TRACE (first 10 lines):\n";
    $traceLines = explode("\n", $e->getTraceAsString());
    foreach (array_slice($traceLines, 0, 10) as $i => $line) {
        echo "      {$i}: {$line}\n";
    }

    // Check if this is related to our recent changes
    if (strpos($e->getMessage(), 'Blob') !== false) {
        echo "\n   🎯 BLOB-RELATED ERROR - May be from our recent fixes!\n";
    }

    if (strpos($e->getMessage(), 'Storage') !== false || strpos($e->getMessage(), 'disk') !== false) {
        echo "\n   🎯 STORAGE-RELATED ERROR - File system issue!\n";
    }

    if (strpos($e->getMessage(), 'encrypt') !== false || strpos($e->getMessage(), 'decrypt') !== false) {
        echo "\n   🎯 ENCRYPTION-RELATED ERROR - EncryptionService issue!\n";
    }
}

echo "\n4. CHECKING RECENT LARAVEL LOGS:\n";
try {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $logContent = file_get_contents($logPath);
        $logLines = explode("\n", $logContent);

        // Get last 20 lines that contain error/exception
        $errorLines = array_filter($logLines, function ($line) {
            return stripos($line, 'error') !== false ||
                stripos($line, 'exception') !== false ||
                stripos($line, 'failed') !== false;
        });

        $recentErrors = array_slice($errorLines, -10);

        if (!empty($recentErrors)) {
            echo "   📋 Recent error log entries:\n";
            foreach ($recentErrors as $error) {
                if (stripos($error, date('Y-m-d')) !== false) { // Today's errors
                    echo "      🔸 " . trim($error) . "\n";
                }
            }
        } else {
            echo "   ✅ No recent errors found in Laravel logs\n";
        }
    } else {
        echo "   ⚠️  Laravel log file not found at: {$logPath}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error reading Laravel logs: " . $e->getMessage() . "\n";
}

echo "\n5. TESTING SIMPLE FILE OPERATIONS:\n";
try {
    // Test basic storage operations
    $testPath = storage_path('app/test_simple_' . time() . '.txt');
    $simpleContent = "Simple file test";

    $written = file_put_contents($testPath, $simpleContent);
    echo "   📝 Simple file write: " . ($written ? "SUCCESS ({$written} bytes)" : "FAILED") . "\n";

    if ($written) {
        $read = file_get_contents($testPath);
        echo "   📖 Simple file read: " . ($read === $simpleContent ? "SUCCESS" : "FAILED") . "\n";
        unlink($testPath);
    }

} catch (\Exception $e) {
    echo "   ❌ Simple file operations failed: " . $e->getMessage() . "\n";
}

echo "\n=== NEW STORAGE EXCEPTION TEST COMPLETE ===\n";
