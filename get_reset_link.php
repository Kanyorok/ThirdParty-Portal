<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emailAddr = 'robertmbugua.kanyoro@gmail.com';
echo "Searching for emails to: $emailAddr\n";

// Fetch the latest email to this user
// Notes:
// 1. The 'To' field in t_Emails is a JSON array or string. It might be stored as `[["Name"=>"email"]]`
// 2. We can look for Subject 'Reset Password Notification'
// 3. We sort by CreatedOn desc.

$email = App\Models\Communication\Email::where('Subject', 'like', '%Reset Password%')
    ->orderBy('CreatedOn', 'desc')
    ->first();

if (!$email) {
    echo "No password reset email found.\n";
    exit(1);
}

echo "Found Email ID: " . $email->EmailID . "\n";
echo "Created On: " . $email->CreatedOn . "\n";
echo "Status: " . $email->Status->name . "\n";
// echo "Body Preview:\n" . substr($email->Body, 0, 500) . "\n...\n";

// Extract content
$body = $email->Body;

// Regex to find url
// Look for href="..."
if (preg_match('/href="([^"]+)"/', $body, $matches)) {
    echo "\n--------------------------------------------------\n";
    echo "RESET LINK FOUND:\n";
    echo $matches[1] . "\n";
    echo "--------------------------------------------------\n";
} else {
    echo "Could not extract link from body.\n";
    echo "Body content:\n$body\n";
}
