#!/usr/bin/env php
<?php
/**
 * Script to find and optionally remove commented routes
 * Commented routes are safe to remove as they're not accessible
 */

$routeFiles = [
    'routes/web.php',
    'routes/api.php',
    'routes/auth.php',
    'routes/budget.php',
    'routes/console.php',
    'routes/crm.php',
    'routes/dms.php',
    'routes/enhanced_grn.php',
    'routes/finance.php',
    'routes/fleet.php',
    'routes/hrms.php',
    'routes/insurance.php',
    'routes/inventory.php',
    'routes/legal.php',
    'routes/portal.php',
    'routes/procurement.php',
    'routes/property.php',
    'routes/thirdparties.php',
    'routes/integrations/crm.php',
    'routes/integrations/dms.php',
    'routes/integrations/property.php',
];

$dryRun = in_array('--dry-run', $argv);
$totalLinesRemoved = 0;
$totalFilesModified = 0;

function removeCommentedRoutes($filePath, $dryRun) {
    if (!file_exists($filePath)) {
        return 0;
    }

    $content = file_get_contents($filePath);
    $lines = explode("\n", $content);
    $newLines = [];
    $removedCount = 0;
    $inCommentedBlock = false;
    $removedLines = [];

    foreach ($lines as $lineNum => $line) {
        $trimmed = trim($line);
        
        // Check if this is a commented route line
        $isCommentedRoute = (
            preg_match('/^\s*\/\/\s*(Route::|Route\s)/', $line) ||
            preg_match('/^\s*\/\/.*->(get|post|put|patch|delete|resource|group|prefix|middleware|name)\(/', $line) ||
            ($inCommentedBlock && preg_match('/^\s*\/\/\s*[\}\]\)]/', $line))
        );

        // Track multi-line commented route blocks
        if (preg_match('/^\s*\/\/\s*Route::|^\s*\/\/\s*Route\s/', $line)) {
            $inCommentedBlock = true;
        }
        
        if ($inCommentedBlock && !preg_match('/^\s*\/\//', $line) && trim($line) !== '') {
            $inCommentedBlock = false;
        }

        if ($isCommentedRoute) {
            $removedCount++;
            $removedLines[] = ($lineNum + 1) . ": " . trim($line);
        } else {
            $newLines[] = $line;
        }
    }

    if ($removedCount > 0) {
        echo "\n📄 " . basename($filePath) . " ($removedCount route line(s) found)\n";
        echo "   " . $filePath . "\n";
        foreach (array_slice($removedLines, 0, 10) as $lineInfo) {
            echo "   🗑️  Line $lineInfo\n";
        }
        if (count($removedLines) > 10) {
            echo "   ... and " . (count($removedLines) - 10) . " more\n";
        }

        if (!$dryRun) {
            file_put_contents($filePath, implode("\n", $newLines));
            echo "   ✅ Removed\n";
        }
    }

    return $removedCount;
}

echo "\n🔍 Scanning route files for commented routes...\n";
if ($dryRun) {
    echo "🔍 DRY RUN MODE - No files will be modified\n";
}
echo "\n";

foreach ($routeFiles as $file) {
    $removed = removeCommentedRoutes($file, $dryRun);
    if ($removed > 0) {
        $totalLinesRemoved += $removed;
        $totalFilesModified++;
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 80) . "\n";

if ($dryRun) {
    echo "🔍 DRY RUN: Would remove $totalLinesRemoved commented route line(s) from $totalFilesModified file(s)\n";
    echo "\nRun without --dry-run to actually remove them.\n";
} else {
    if ($totalLinesRemoved > 0) {
        echo "✅ Removed $totalLinesRemoved commented route line(s) from $totalFilesModified file(s)\n";
        echo "\n⚠️  Review with: git diff routes/\n";
    } else {
        echo "✅ No commented routes found!\n";
    }
}

echo "\n";
exit(0);
