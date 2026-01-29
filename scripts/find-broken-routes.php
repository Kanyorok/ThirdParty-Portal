#!/usr/bin/env php
<?php
/**
 * Find and remove routes pointing to non-existent controllers
 * This is a CRITICAL security fix - routes to missing controllers can cause 500 errors
 */

$routeFiles = glob('routes/*.php') + glob('routes/**/*.php');

$totalIssues = 0;
$issuesByFile = [];

foreach ($routeFiles as $routeFile) {
    $content = file_get_contents($routeFile);
    $lines = explode("\n", $content);
    
    // Extract all controller imports
    $imports = [];
    foreach ($lines as $line) {
        if (preg_match('/use\s+(App\\\\Http\\\\Controllers\\\\[^;]+);/', $line, $matches)) {
            $fullClass = $matches[1];
            $className = basename(str_replace('\\', '/', $fullClass));
            $filePath = 'app/Http/Controllers/' . str_replace('App\\Http\\Controllers\\', '', str_replace('\\', '/', $fullClass)) . '.php';
            
            $imports[$className] = [
                'class' => $fullClass,
                'file' => $filePath,
                'exists' => file_exists($filePath),
            ];
        }
    }
    
    // Find controllers used in routes
    $usedControllers = [];
    foreach ($lines as $lineNum => $line) {
        // Match patterns like: [ControllerClass::class, or ControllerClass@method
        if (preg_match('/([A-Za-z]+Controller)::(class|[a-z]+)|\'([A-Za-z]+Controller)@/', $line, $matches)) {
            $controller = $matches[1] ?? $matches[3];
            if (isset($imports[$controller]) && !$imports[$controller]['exists']) {
                $usedControllers[] = [
                    'line' => $lineNum + 1,
                    'controller' => $controller,
                    'code' => trim($line),
                ];
            }
        }
    }
    
    if (!empty($usedControllers)) {
        $totalIssues += count($usedControllers);
        $issuesByFile[$routeFile] = [
            'controllers' => $usedControllers,
            'imports' => array_filter($imports, fn($i) => !$i['exists']),
        ];
    }
}

if (empty($issuesByFile)) {
    echo "\n✅ No routes pointing to non-existent controllers found!\n\n";
    exit(0);
}

echo "\n🚨 CRITICAL SECURITY ISSUES FOUND\n";
echo str_repeat("=", 80) . "\n\n";
echo "Found $totalIssues route(s) pointing to non-existent controllers!\n\n";

foreach ($issuesByFile as $file => $issues) {
    echo "📄 $file\n";
    echo str_repeat("-", 80) . "\n";
    
    echo "\n❌ Missing Controllers:\n";
    foreach ($issues['imports'] as $controller => $info) {
        echo "   • $controller\n";
        echo "     Expected: {$info['file']}\n";
    }
    
    echo "\n🔴 Affected Routes (Line Numbers):\n";
    foreach ($issues['controllers'] as $route) {
        echo "   Line {$route['line']}: {$route['controller']}\n";
        echo "     {$route['code']}\n";
    }
    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "⚠️  ACTION REQUIRED:\n";
echo "These routes will cause 500 errors if accessed!\n";
echo "1. Comment out or remove these routes\n";
echo "2. Remove the unused controller imports\n";
echo "3. Or create the missing controller files\n\n";

exit(1);
