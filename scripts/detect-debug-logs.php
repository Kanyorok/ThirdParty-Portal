#!/usr/bin/env php
<?php
/**
 * Custom script to detect debug/development log statements in PHP files
 * This catches Laravel-specific debug logging that should not go to production
 */

$patterns = [
    // Debug log statements with development markers
    '/Log::(info|debug)\s*\(\s*[\'"](?:.*(?:BEFORE|AFTER|SKIPPED|TODO:|TEST|DEBUG_|DUMP|Creating|Successfully|About to).*?)[\'"]/',
    
    // dd(), dump() - Laravel debug functions (commented or not)
    '/\b(dd|dump)\s*\(/',
    
    // ray() - Spatie Ray debug tool
    '/\bray\s*\(/',
    
    // die() or exit() with messages (debugging)
    '/\b(die|exit)\s*\(\s*[\'"]/',
    
    // Commented out code detection - ANY commented function/class/control structure
    '/^\s*\/\/\s*(public|private|protected|function|class|if|for|while|foreach|return|use |namespace )\s*/',
];

$directories = [
    'app',
    'config',
    'database/factories',
    'database/seeders',
    'routes',
];

$excludePatterns = [
    'vendor',
    'storage',
    'node_modules',
    'bootstrap/cache',
];

$errors = [];
$totalFiles = 0;
$totalIssues = 0;

function shouldExclude($path, $excludePatterns) {
    foreach ($excludePatterns as $pattern) {
        if (strpos($path, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function scanDirectory($dir, $patterns, $excludePatterns, &$errors, &$totalFiles, &$totalIssues) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filePath = $file->getPathname();
            
            if (shouldExclude($filePath, $excludePatterns)) {
                continue;
            }

            $totalFiles++;
            $content = file_get_contents($filePath);
            $lines = explode("\n", $content);

            foreach ($lines as $lineNumber => $line) {
                foreach ($patterns as $patternName => $pattern) {
                    if (preg_match($pattern, $line)) {
                        $errors[] = [
                            'file' => $filePath,
                            'line' => $lineNumber + 1,
                            'content' => trim($line),
                            'pattern' => is_string($patternName) ? $patternName : 'debug_statement'
                        ];
                        $totalIssues++;
                    }
                }
            }
        }
    }
}

echo "\n🔍 Scanning for debug/development log statements...\n\n";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "Scanning: $dir/\n";
        scanDirectory($dir, $patterns, $excludePatterns, $errors, $totalFiles, $totalIssues);
    }
}

echo "\n" . str_repeat('=', 80) . "\n";
echo "SCAN RESULTS\n";
echo str_repeat('=', 80) . "\n\n";

if (empty($errors)) {
    echo "✅ No debug/development log statements found!\n";
    echo "Scanned $totalFiles files.\n\n";
    exit(0);
} else {
    echo "❌ Found $totalIssues debug/development log statement(s) in $totalFiles files:\n\n";
    
    $groupedErrors = [];
    foreach ($errors as $error) {
        $groupedErrors[$error['file']][] = $error;
    }

    foreach ($groupedErrors as $file => $fileErrors) {
        echo "\n📄 " . $file . " (" . count($fileErrors) . " issue(s))\n";
        echo str_repeat('-', 80) . "\n";
        
        foreach ($fileErrors as $error) {
            echo sprintf(
                "   Line %4d: %s\n",
                $error['line'],
                substr($error['content'], 0, 100)
            );
        }
    }
    
    echo "\n" . str_repeat('=', 80) . "\n";
    echo "Please remove these debug statements before deploying to production.\n\n";
    exit(1);
}
