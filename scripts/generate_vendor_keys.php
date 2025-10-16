<?php
/**
 * Vendor Key Generation Script
 * 
 * Run this ONCE to generate your Ed25519 key pair for licensing
 * Keep the private key SECURE and never expose it!
 */

if (!extension_loaded('sodium')) {
    die("❌ Error: Sodium extension is required for Ed25519 key generation\n\n" .
        "Install instructions:\n" .
        "- Windows: Uncomment 'extension=sodium' in php.ini\n" .
        "- Ubuntu: sudo apt-get install php-sodium\n" .
        "- macOS: brew install libsodium\n");
}

echo "🔐 Generating Ed25519 Vendor Key Pair\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Generate Ed25519 keypair
    $keypair = sodium_crypto_sign_keypair();
    $publicKey = base64_encode(sodium_crypto_sign_publickey($keypair));
    $privateKey = base64_encode(sodium_crypto_sign_secretkey($keypair));

    echo "✅ Keys generated successfully!\n\n";

    // Display public key for .env
    echo "📋 PUBLIC KEY (add to .env file):\n";
    echo str_repeat("-", 40) . "\n";
    echo "LICENSING_VENDOR_PUBLIC_KEY={$publicKey}\n\n";

    // Display private key warning
    echo "🔑 PRIVATE KEY (keep EXTREMELY secure!):\n";
    echo str_repeat("-", 40) . "\n";
    echo "{$privateKey}\n\n";

    // Save keys to secure files
    $timestamp = date('Y-m-d_H-i-s');
    
    // Save private key
    file_put_contents("private_key_{$timestamp}.txt", $privateKey);
    echo "💾 Private key saved to: private_key_{$timestamp}.txt\n";
    
    // Save public key
    file_put_contents("public_key_{$timestamp}.txt", $publicKey);
    echo "💾 Public key saved to: public_key_{$timestamp}.txt\n\n";

    // Security warnings
    echo "🚨 CRITICAL SECURITY NOTES:\n";
    echo str_repeat("-", 40) . "\n";
    echo "1. NEVER commit the private key to version control\n";
    echo "2. Store private key in a secure password manager\n";
    echo "3. Only the public key goes in your ERP's .env file\n";
    echo "4. Private key is used ONLY for signing licenses\n";
    echo "5. Delete the private key file from this directory after securing it\n\n";

    echo "🎯 Next Steps:\n";
    echo "1. Copy the public key to your .env file\n";
    echo "2. Store the private key securely (delete local file)\n";
    echo "3. Run 'php artisan config:clear' to reload config\n";
    echo "4. Test with the sample license generator\n";

} catch (Exception $e) {
    echo "❌ Error generating keys: " . $e->getMessage() . "\n";
    exit(1);
}
