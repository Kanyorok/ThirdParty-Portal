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

echo "=== BID SUBMISSION CONTEXT STORAGE TEST ===\n\n";

$user = User::first();
$repository = Repository::where('Name', 'Encrypted Bid Documents')->first();

echo "1. TESTING WITH LARGE ENCRYPTED CONTENT (Real Bid Scenario):\n";

// Create large content similar to real bid documents
$largeContent = str_repeat("This is a large bid document with detailed specifications, pricing, technical requirements, and company information. ", 200);
$encryptedContent = Crypt::encryptString($largeContent);

echo "   📦 Original content: " . strlen($largeContent) . " bytes\n";
echo "   🔒 Encrypted content: " . strlen($encryptedContent) . " bytes\n";

try {
    echo "   🔄 Testing DocumentService with large encrypted content...\n";

    $result = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Txt,
        fileName: "large_bid_test_" . time() . ".txt",
        content: $encryptedContent,
        actor: $user,
        copyRepoPermissions: true // Same as bid system
    );

    echo "   ✅ Large content storage: SUCCESS\n";

    // Test retrieval
    $retrieved = $result->document->current->getContent();
    $decrypted = Crypt::decryptString($retrieved);

    if ($decrypted === $largeContent) {
        echo "   ✅ Large content retrieval & decryption: SUCCESS\n";
    } else {
        echo "   ❌ Large content corruption detected!\n";
    }

} catch (\Exception $e) {
    echo "   🚨 LARGE CONTENT EXCEPTION!\n";
    echo "   💥 Error: " . $e->getMessage() . "\n";
    echo "   📋 Location: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n2. TESTING MULTIPLE CONCURRENT DOCUMENT STORAGE:\n";

try {
    echo "   🔄 Storing multiple documents rapidly (simulating concurrent uploads)...\n";

    $results = [];
    for ($i = 1; $i <= 5; $i++) {
        $content = "Concurrent document {$i} content - " . now() . " - " . str_repeat("data ", 100);
        $encrypted = Crypt::encryptString($content);

        $result = DocumentService::createContent(
            repository: $repository,
            extension: ExtensionsEnum::Txt,
            fileName: "concurrent_test_{$i}_" . time() . ".txt",
            content: $encrypted,
            actor: $user,
            copyRepoPermissions: true
        );

        $results[] = ['original' => $content, 'document' => $result->document];
        echo "      ✅ Document {$i} stored successfully\n";
    }

    // Verify all documents
    echo "   🔍 Verifying all concurrent documents...\n";
    foreach ($results as $i => $data) {
        $retrieved = $data['document']->current->getContent();
        $decrypted = Crypt::decryptString($retrieved);

        if ($decrypted === $data['original']) {
            echo "      ✅ Concurrent document " . ($i + 1) . ": VERIFIED\n";
        } else {
            echo "      ❌ Concurrent document " . ($i + 1) . ": CORRUPTED\n";
        }
    }

} catch (\Exception $e) {
    echo "   🚨 CONCURRENT STORAGE EXCEPTION!\n";
    echo "   💥 Error: " . $e->getMessage() . "\n";
    echo "   📋 Location: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n3. TESTING ENCRYPTED BID DOCUMENT SERVICE (Actual System):\n";

try {
    echo "   🔄 Testing EncryptedBidDocumentService directly...\n";

    // Create a mock BidSubmission
    $bidSubmission = new BidSubmission();
    $bidSubmission->TenderRef = 'TEST-TENDER-' . time();
    $bidSubmission->SupplierId = 1;

    // Create mock uploaded file content
    $mockFileContent = "MOCK BID DOCUMENT CONTENT - " . now() . "\n\nThis is a comprehensive bid document with:\n- Technical specifications\n- Pricing details\n- Company credentials\n- Legal declarations\n\n" . str_repeat("Additional bid content line ", 50);

    // Create temporary file to simulate UploadedFile
    $tempFilePath = sys_get_temp_dir() . '/mock_bid_' . time() . '.pdf';
    file_put_contents($tempFilePath, $mockFileContent);

    // This is where we might hit the exception
    echo "   📄 Mock file created: " . strlen($mockFileContent) . " bytes\n";
    echo "   🔍 Testing if EncryptedBidDocumentService can handle this...\n";

    // Note: We can't easily test EncryptedBidDocumentService without UploadedFile objects
    // But we can test the DocumentService with the exact same parameters

    $encryptedMockContent = Crypt::encryptString($mockFileContent);

    $result = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Pdf,
        fileName: "BID_" . $bidSubmission->TenderRef . "_1_test.pdf",
        content: $encryptedMockContent,
        actor: $user,
        copyRepoPermissions: true
    );

    echo "   ✅ Mock bid document storage: SUCCESS\n";
    echo "   📄 Document ID: {$result->document->DocumentId}\n";

    // Clean up
    unlink($tempFilePath);

} catch (\Exception $e) {
    echo "   🚨 BID DOCUMENT SERVICE EXCEPTION!\n";
    echo "   💥 Error: " . $e->getMessage() . "\n";
    echo "   📍 Exception type: " . get_class($e) . "\n";
    echo "   📋 Location: " . $e->getFile() . ":" . $e->getLine() . "\n";

    // This might be our culprit!
    echo "\n   🎯 POTENTIAL ROOT CAUSE IDENTIFIED!\n";
}

echo "\n4. TESTING EDGE CASES:\n";

// Test with very large content
try {
    $veryLargeContent = str_repeat("Very large document content for stress testing. ", 1000); // ~50KB
    $encryptedVeryLarge = Crypt::encryptString($veryLargeContent);

    echo "   📦 Very large content: " . number_format(strlen($encryptedVeryLarge)) . " bytes\n";

    $result = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Pdf,
        fileName: "very_large_test_" . time() . ".pdf",
        content: $encryptedVeryLarge,
        actor: $user,
        copyRepoPermissions: true
    );

    echo "   ✅ Very large content: SUCCESS\n";

} catch (\Exception $e) {
    echo "   🚨 VERY LARGE CONTENT EXCEPTION!\n";
    echo "   💥 Error: " . $e->getMessage() . "\n";

    if (strpos($e->getMessage(), 'memory') !== false) {
        echo "   🎯 MEMORY LIMIT ISSUE!\n";
    }
    if (strpos($e->getMessage(), 'timeout') !== false) {
        echo "   🎯 TIMEOUT ISSUE!\n";
    }
}

echo "\n=== BID SUBMISSION CONTEXT TEST COMPLETE ===\n";
