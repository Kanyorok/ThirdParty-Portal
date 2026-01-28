#!/usr/bin/env php
<?php
/**
 * Script to automatically remove commented-out code from PHP files
 * This removes lines that match our debug detection patterns
 */

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

// Patterns for lines to remove
$removePatterns = [
    // Commented out functions, classes, control structures
    '/^\s*\/\/\s*(public|private|protected|function|class|if|for|while|foreach|return|use |namespace |protected )\s*/',
    // Commented out dd(), dump(), ray()
    '/^\s*\/\/.*\b(dd|dump|ray)\s*\(/',
    // Lines that are just comment markers with function calls inside
    '/^\s*\/\/\s*\$/',
];

$totalFilesModified = 0;
$totalLinesRemoved = 0;
$dryRun = in_array('--dry-run', $argv);

function shouldExclude($path, $excludePatterns) {
    foreach ($excludePatterns as $pattern) {
        if (strpos($path, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

function shouldRemoveLine($line, $patterns) {
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $line)) {
            return true;
        }
    }
    return false;
}

function processFile($filePath, $patterns, $dryRun) {
    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $newLines = [];
    $removedCount = 0;
    $removedLineNumbers = [];
    
    foreach ($lines as $lineNumber => $line) {
        if (shouldRemoveLine($line, $patterns)) {
            $removedCount++;
            $removedLineNumbers[] = ($lineNumber + 1) . ": " . trim($line);
        } else {
            $newLines[] = $line;
        }
    }
    
    if ($removedCount > 0) {
        if (!$dryRun) {
            file_put_contents($filePath, implode("\n", $newLines));
        }
        
        echo "\n📄 " . basename($filePath) . " ($removedCount line(s) removed)\n";
        echo "   " . dirname($filePath) . "\n";
        foreach (array_slice($removedLineNumbers, 0, 5) as $lineInfo) {
            echo "   ❌ Line $lineInfo\n";
        }
        if (count($removedLineNumbers) > 5) {
            echo "   ... and " . (count($removedLineNumbers) - 5) . " more\n";
        }
    }
    
    return $removedCount;
}

function processDirectory($dir, $patterns, $excludePatterns, $dryRun, &$totalFilesModified, &$totalLinesRemoved) {
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
            
            $removedCount = processFile($filePath, $patterns, $dryRun);
            if ($removedCount > 0) {
                $totalFilesModified++;
                $totalLinesRemoved += $removedCount;
            }
        }
    }
}

echo "\n🧹 Removing commented-out code from PHP files...\n";
if ($dryRun) {
    echo "🔍 DRY RUN MODE - No files will be modified\n";
}
echo "\n";

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        echo "Scanning: $dir/\n";
        processDirectory($dir, $removePatterns, $excludePatterns, $dryRun, $totalFilesModified, $totalLinesRemoved);
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "CLEANUP SUMMARY\n";
echo str_repeat("=", 80) . "\n";

if ($dryRun) {
    echo "🔍 DRY RUN: Would remove $totalLinesRemoved line(s) from $totalFilesModified file(s)\n";
    echo "\nRun without --dry-run to actually remove the lines.\n";
} else {
    if ($totalLinesRemoved > 0) {
        echo "✅ Removed $totalLinesRemoved commented line(s) from $totalFilesModified file(s)\n";
        echo "\n⚠️  IMPORTANT: Review the changes with 'git diff' before committing!\n";
    } else {
        echo "✅ No commented-out code found!\n";
    }
}

echo "\n";
exit(0);
