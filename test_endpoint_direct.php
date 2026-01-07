<?php
// Test endpoint without middleware
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a request to the endpoint
$request = Illuminate\Http\Request::create(
    'http://127.0.0.1:8000/api/procurement/rfq-suppliers',
    'GET'
);

echo "=== Testing Endpoint Directly ===\n";
echo "Memory limit: " . ini_get('memory_limit') . "\n";
echo "Starting memory: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB\n\n";

try {
    $response = $kernel->handle($request);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Body: " . substr($response->getContent(), 0, 500) . "\n";
    echo "Peak Memory: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
    
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Peak Memory: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
}

$kernel->terminate($request, $response ?? null);
