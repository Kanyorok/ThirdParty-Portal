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
use Illuminate\Support\Facades\Crypt;

echo "=== CONTENT MISMATCH DEBUG TEST ===\n\n";

$user = User::first();
$repository = Repository::where('Name', 'Encrypted Bid Documents')->first();

// Create test content
$originalContent = "CONTENT MISMATCH DEBUG TEST - " . now();
$fileName = "debug_mismatch_" . time() . ".txt";

echo "1. ORIGINAL CONTENT ANALYSIS:\n";
echo "   📄 Original content: '{$originalContent}'\n";
echo "   📏 Original length: " . strlen($originalContent) . " bytes\n";
echo "   🔤 Original hash: " . md5($originalContent) . "\n";

echo "\n2. STORING CONTENT THROUGH DOCUMENTSERVICE:\n";

try {
    $result = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Txt,
        fileName: $fileName,
        content: $originalContent,
        actor: $user,
        copyRepoPermissions: false
    );
    
    echo "   ✅ Storage successful\n";
    echo "   📄 Document ID: {$result->document->DocumentId}\n";
    echo "   📄 Version ID: {$result->document->current->Id}\n";
    
} catch (\Exception $e) {
    echo "   ❌ Storage failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n3. CHECKING WHAT WAS ACTUALLY STORED IN DATABASE:\n";

$storedVersion = $result->document->current;

echo "   📋 Database Blob field:\n";
if ($storedVersion->Blob) {
    echo "      Content: '{$storedVersion->Blob}'\n";
    echo "      Length: " . strlen($storedVersion->Blob) . " bytes\n";
    echo "      Hash: " . md5($storedVersion->Blob) . "\n";
} else {
    echo "      ⚠️  Blob field is empty\n";
}

echo "   📋 Database Path field: '{$storedVersion->Path}'\n";

// Check if file exists at Path
$fullPath = storage_path('app/' . $storedVersion->Path);
echo "   📂 File system path: {$fullPath}\n";
echo "   📂 File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "\n";

if (file_exists($fullPath)) {
    $fileContent = file_get_contents($fullPath);
    echo "   📂 File content: '{$fileContent}'\n";
    echo "   📂 File length: " . strlen($fileContent) . " bytes\n";
    echo "   📂 File hash: " . md5($fileContent) . "\n";
}

echo "\n4. TESTING getContent() METHOD:\n";

try {
    $retrievedContent = $storedVersion->getContent();
    echo "   📤 Retrieved content: '{$retrievedContent}'\n";
    echo "   📤 Retrieved length: " . strlen($retrievedContent) . " bytes\n";
    echo "   📤 Retrieved hash: " . md5($retrievedContent) . "\n";
    
    echo "\n5. CONTENT COMPARISON:\n";
    if ($originalContent === $retrievedContent) {
        echo "   ✅ PERFECT MATCH!\n";
        echo "      Original and retrieved content are identical\n";
    } else {
        echo "   ❌ CONTENT MISMATCH!\n";
        echo "      Original: '{$originalContent}' (" . strlen($originalContent) . " bytes)\n";
        echo "      Retrieved: '{$retrievedContent}' (" . strlen($retrievedContent) . " bytes)\n";
        
        // Character-by-character comparison
        echo "\n   🔍 Character-by-character analysis:\n";
        $maxLen = max(strlen($originalContent), strlen($retrievedContent));
        for ($i = 0; $i < min($maxLen, 50); $i++) { // Check first 50 chars
            $origChar = $i < strlen($originalContent) ? $originalContent[$i] : '[END]';
            $retrChar = $i < strlen($retrievedContent) ? $retrievedContent[$i] : '[END]';
            $match = $origChar === $retrChar ? '✅' : '❌';
            echo "      Position {$i}: '{$origChar}' vs '{$retrChar}' {$match}\n";
            
            if ($origChar !== $retrChar) {
                echo "      🚨 First difference at position {$i}\n";
                break;
            }
        }
    }
    
} catch (\Exception $e) {
    echo "   ❌ getContent() failed: " . $e->getMessage() . "\n";
}

echo "\n6. TESTING WITH ENCRYPTED CONTENT (like bid system):\n";

$bidContent = "ENCRYPTED BID CONTENT TEST - " . now();
$encryptedContent = Crypt::encryptString($bidContent);
$encFileName = "debug_encrypted_" . time() . ".enc";

echo "   📄 Bid content: '{$bidContent}'\n";
echo "   🔒 Encrypted length: " . strlen($encryptedContent) . " bytes\n";

try {
    $encResult = DocumentService::createContent(
        repository: $repository,
        extension: ExtensionsEnum::Txt,
        fileName: $encFileName,
        content: $encryptedContent,
        actor: $user,
        copyRepoPermissions: false
    );
    
    $retrievedEncrypted = $encResult->document->current->getContent();
    
    if ($encryptedContent === $retrievedEncrypted) {
        echo "   ✅ Encrypted content matches!\n";
        
        // Test decryption
        $decrypted = Crypt::decryptString($retrievedEncrypted);
        if ($decrypted === $bidContent) {
            echo "   ✅ Decryption successful! Content integrity verified.\n";
        } else {
            echo "   ❌ Decryption failed or content corrupted\n";
        }
    } else {
        echo "   ❌ Encrypted content mismatch!\n";
        echo "      Original encrypted length: " . strlen($encryptedContent) . "\n";
        echo "      Retrieved encrypted length: " . strlen($retrievedEncrypted) . "\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Encrypted content test failed: " . $e->getMessage() . "\n";
}

echo "\n=== CONTENT MISMATCH DEBUG COMPLETE ===\n";
