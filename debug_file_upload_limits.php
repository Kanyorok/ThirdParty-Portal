<?php

echo "=== FILE UPLOAD LIMITS DEBUG ===\n\n";

echo "1. PHP CONFIGURATION:\n";
echo "   📋 upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   📋 post_max_size: " . ini_get('post_max_size') . "\n";
echo "   📋 max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "   📋 memory_limit: " . ini_get('memory_limit') . "\n";
echo "   📋 max_execution_time: " . ini_get('max_execution_time') . " seconds\n";
echo "   📋 max_input_time: " . ini_get('max_input_time') . " seconds\n";

echo "\n2. LARAVEL VALIDATION VS PHP LIMITS:\n";
$laravelMaxMB = 10; // From validation: max:10240 (KB) = 10MB
$phpUploadMaxBytes = ini_get('upload_max_filesize');
$phpPostMaxBytes = ini_get('post_max_size');

// Convert PHP limits to MB for comparison
function convertToMB($size) {
    $size = trim($size);
    $last = strtolower($size[strlen($size)-1]);
    $size = (float) $size;
    switch($last) {
        case 'g': $size *= 1024;
        case 'm': $size *= 1024;
        case 'k': $size *= 1024;
    }
    return $size / (1024 * 1024);
}

$phpUploadMB = convertToMB($phpUploadMaxBytes);
$phpPostMB = convertToMB($phpPostMaxBytes);

echo "   📊 Laravel max per file: {$laravelMaxMB} MB\n";
echo "   📊 PHP upload_max_filesize: {$phpUploadMB} MB\n";
echo "   📊 PHP post_max_size: {$phpPostMB} MB\n";

if ($phpUploadMB < $laravelMaxMB) {
    echo "   ⚠️  PHP upload limit is SMALLER than Laravel validation!\n";
    echo "       This could cause uploads to fail before reaching Laravel validation!\n";
}

if ($phpPostMB < ($laravelMaxMB * 5)) { // Assume 5 files max
    echo "   ⚠️  PHP post_max_size might be too small for multiple files!\n";
}

echo "\n3. MIME TYPE DETECTION ISSUES:\n";
$allowedMimes = ['pdf', 'doc', 'docx', 'zip'];
echo "   📋 Allowed MIME types in validation: " . implode(', ', $allowedMimes) . "\n";

echo "   📋 Common MIME type variations:\n";
$mimeVariations = [
    'pdf' => ['application/pdf'],
    'doc' => ['application/msword'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'zip' => ['application/zip', 'application/x-zip-compressed'],
    'other' => ['application/octet-stream', 'text/plain']
];

foreach ($mimeVariations as $type => $mimes) {
    echo "      {$type}: " . implode(', ', $mimes) . "\n";
}

echo "\n   ⚠️  Portal might be sending files with MIME types not in the allowed list!\n";

echo "\n4. MEMORY USAGE CALCULATION:\n";
$memoryLimitMB = convertToMB(ini_get('memory_limit'));
echo "   📊 PHP memory_limit: {$memoryLimitMB} MB\n";

// DocumentService loads entire file into memory for encryption
$estimatedMemoryPerFile = $laravelMaxMB * 3; // Original + Encrypted + Processing overhead
echo "   📊 Estimated memory per 10MB file: ~{$estimatedMemoryPerFile} MB\n";

if ($estimatedMemoryPerFile > ($memoryLimitMB * 0.5)) {
    echo "   ⚠️  Memory usage might exceed 50% of PHP memory limit!\n";
    echo "       This could cause 'Allowed memory size exhausted' errors!\n";
}

echo "\n5. TEMPORARY UPLOAD DIRECTORY:\n";
$uploadTmpDir = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();
echo "   📋 Upload tmp directory: {$uploadTmpDir}\n";

if (is_writable($uploadTmpDir)) {
    echo "   ✅ Upload tmp directory is writable\n";
} else {
    echo "   ❌ Upload tmp directory is NOT writable!\n";
}

$tmpSpaceBytes = disk_free_space($uploadTmpDir);
$tmpSpaceMB = round($tmpSpaceBytes / 1024 / 1024, 2);
echo "   📊 Free space in tmp directory: {$tmpSpaceMB} MB\n";

if ($tmpSpaceMB < 100) {
    echo "   ⚠️  Low disk space in tmp directory - uploads might fail!\n";
}

echo "\n=== RECOMMENDATIONS ===\n";

$recommendations = [];
if ($phpUploadMB < $laravelMaxMB) {
    $recommendations[] = "Increase PHP upload_max_filesize to at least 10M";
}
if ($phpPostMB < 50) {
    $recommendations[] = "Increase PHP post_max_size to at least 50M (for multiple files)";
}
if ($memoryLimitMB < 256) {
    $recommendations[] = "Increase PHP memory_limit to at least 256M";
}
if (!is_writable($uploadTmpDir)) {
    $recommendations[] = "Fix write permissions on upload tmp directory";
}

if (empty($recommendations)) {
    echo "✅ PHP configuration looks good for file uploads!\n";
    echo "\nThe storage exception must be happening at a different level.\n";
    echo "Consider checking:\n";
    echo "- Network timeout during large uploads\n";
    echo "- Portal sending files with unexpected MIME types\n";
    echo "- Concurrent upload handling\n";
} else {
    echo "⚠️  CONFIGURATION ISSUES FOUND:\n";
    foreach ($recommendations as $i => $rec) {
        echo "   " . ($i + 1) . ". {$rec}\n";
    }
}

echo "\n=== FILE UPLOAD LIMITS DEBUG COMPLETE ===\n";

