#!/usr/bin/env php
<?php
/**
 * Script to identify potentially unused files in the Laravel application
 * This performs static analysis to find orphaned files
 */

echo "\n🔍 UNUSED FILE DETECTOR\n";
echo str_repeat("=", 80) . "\n\n";

// Categories to analyze
$categories = [
    'Controllers' => [
        'path' => 'app/Http/Controllers',
        'namespace' => 'App\\Http\\Controllers',
        'extension' => 'php',
    ],
    'Models' => [
        'path' => 'app/Models',
        'namespace' => 'App\\Models',
        'extension' => 'php',
    ],
    'Services' => [
        'path' => 'app/Services',
        'namespace' => 'App\\Services',
        'extension' => 'php',
    ],
    'Views' => [
        'path' => 'resources/views',
        'extension' => 'blade.php',
    ],
    'Middleware' => [
        'path' => 'app/Http/Middleware',
        'namespace' => 'App\\Http\\Middleware',
        'extension' => 'php',
    ],
];

$searchPaths = [
    'app/',
    'routes/',
    'resources/views/',
    'config/',
    'database/seeders/',
    'database/factories/',
    'tests/',
];

function getAllFiles($directory, $extension = 'php') {
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && (
            $extension === '*' || 
            str_ends_with($file->getFilename(), '.' . $extension) ||
            str_ends_with($file->getFilename(), $extension)
        )) {
            $files[] = $file->getPathname();
        }
    }

    return $files;
}

function getClassName($filePath) {
    $content = file_get_contents($filePath);
    
    // Extract class name
    if (preg_match('/class\s+([A-Za-z0-9_]+)/', $content, $matches)) {
        return $matches[1];
    }
    
    return null;
}

function getViewName($filePath) {
    // Convert path to Laravel view notation
    $relativePath = str_replace('resources/views/', '', $filePath);
    $relativePath = str_replace('.blade.php', '', $relativePath);
    return str_replace('/', '.', $relativePath);
}

function isFileUsed($filePath, $className, $searchPaths, $category) {
    $usageCount = 0;
    $usageLocations = [];

    // Build search patterns based on category
    $patterns = [];
    
    if ($category === 'Controllers') {
        $patterns = [
            $className . '::class',           // Route::get('/', [HomeController::class, 'index'])
            $className . '@',                 // Old Laravel syntax
            "use.*{$className}",              // Import statement
            "new {$className}",               // Instantiation
        ];
    } elseif ($category === 'Models') {
        $patterns = [
            $className . '::',                // Model::find(), Model::where()
            "use.*{$className}",              // Import
            "new {$className}",               // new Model()
            "{$className}\\s+\\\$",           // Model $variable
            "hasOne\\({$className}",          // Relationships
            "hasMany\\({$className}",
            "belongsTo\\({$className}",
            "belongsToMany\\({$className}",
        ];
    } elseif ($category === 'Services') {
        $patterns = [
            $className . '::class',
            "use.*{$className}",
            "new {$className}",
            "{$className}\\s+\\\$",
        ];
    } elseif ($category === 'Views') {
        $viewName = getViewName($filePath);
        $patterns = [
            "view\\(['\"]" . preg_quote($viewName, '/') . "['\"]",  // view('path.to.view')
            "View::make\\(['\"]" . preg_quote($viewName, '/') . "['\"]",
            "@include\\(['\"]" . preg_quote($viewName, '/') . "['\"]",
            "@extends\\(['\"]" . preg_quote($viewName, '/') . "['\"]",
        ];
    } elseif ($category === 'Middleware') {
        $patterns = [
            $className . '::class',
            "use.*{$className}",
            "['\"]" . $className . "['\"]",   // 'middleware' => 'MiddlewareName'
        ];
    }

    // Search in all relevant files
    foreach ($searchPaths as $searchPath) {
        if (!is_dir($searchPath)) {
            continue;
        }

        $files = getAllFiles($searchPath, '*');
        
        foreach ($files as $file) {
            // Skip the file itself
            if ($file === $filePath) {
                continue;
            }

            // Skip vendor and node_modules
            if (strpos($file, 'vendor/') !== false || strpos($file, 'node_modules/') !== false) {
                continue;
            }

            $content = @file_get_contents($file);
            if ($content === false) {
                continue;
            }

            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/i', $content)) {
                    $usageCount++;
                    $usageLocations[] = $file;
                    break; // Count each file only once
                }
            }

            // Stop after finding a few usages to save time
            if ($usageCount >= 3) {
                break 2;
            }
        }
    }

    return [
        'used' => $usageCount > 0,
        'count' => $usageCount,
        'locations' => array_slice($usageLocations, 0, 3),
    ];
}

$unusedFiles = [];

foreach ($categories as $categoryName => $config) {
    echo "Analyzing {$categoryName}...\n";
    
    if (!is_dir($config['path'])) {
        echo "  ⚠️  Directory not found: {$config['path']}\n\n";
        continue;
    }

    $files = getAllFiles($config['path'], $config['extension']);
    $unusedInCategory = [];

    foreach ($files as $file) {
        $className = $categoryName === 'Views' ? null : getClassName($file);
        
        if (!$className && $categoryName !== 'Views') {
            continue; // Skip files without class definitions
        }

        $relativePath = str_replace(getcwd() . '/', '', $file);
        
        // Check if file is used
        $usage = isFileUsed($file, $className, $searchPaths, $categoryName);
        
        if (!$usage['used']) {
            $unusedInCategory[] = [
                'path' => $relativePath,
                'class' => $className ?? getViewName($file),
                'size' => filesize($file),
            ];
        }
    }

    echo "  Total: " . count($files) . " files\n";
    echo "  Unused: " . count($unusedInCategory) . " files\n\n";

    if (count($unusedInCategory) > 0) {
        $unusedFiles[$categoryName] = $unusedInCategory;
    }
}

// Generate report
echo "\n" . str_repeat("=", 80) . "\n";
echo "UNUSED FILES REPORT\n";
echo str_repeat("=", 80) . "\n\n";

if (empty($unusedFiles)) {
    echo "✅ No obviously unused files detected!\n\n";
    exit(0);
}

$totalUnused = 0;
foreach ($unusedFiles as $category => $files) {
    $totalUnused += count($files);
    echo "📁 {$category} ({$count} files):\n";
    echo str_repeat("-", 80) . "\n";
    
    foreach (array_slice($files, 0, 20) as $file) {
        $sizeKB = round($file['size'] / 1024, 2);
        echo "  🗑️  {$file['path']}\n";
        echo "      Class: {$file['class']}, Size: {$sizeKB} KB\n";
    }
    
    if (count($files) > 20) {
        echo "  ... and " . (count($files) - 20) . " more\n";
    }
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "⚠️  TOTAL: {$totalUnused} potentially unused files\n";
echo str_repeat("=", 80) . "\n\n";

echo "📝 RECOMMENDATIONS:\n";
echo "1. Review each file manually before deletion\n";
echo "2. Check if files are used via dynamic calls (eval, variable functions)\n";
echo "3. Check if files are used in JavaScript/frontend code\n";
echo "4. Start with obviously unused files (test controllers, example files)\n";
echo "5. Create a backup/branch before deletion\n";
echo "6. Run full test suite after deletion\n\n";

echo "💾 Saving detailed report to: unused-files-report.json\n";
file_put_contents('unused-files-report.json', json_encode($unusedFiles, JSON_PRETTY_PRINT));

echo "\n✅ Report saved! Review carefully before deleting any files.\n\n";

exit(0);
