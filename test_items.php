<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Procurement\RequisitionItemsController;
use Illuminate\Http\Request;

$controller = new RequisitionItemsController();

// Test with a sample type ID - let's try 1 first
$request = new Request();
$request->merge(['type' => 1]);

try {
    $response = $controller->getGenericItemsByType($request, 1);
    echo "Response: " . $response . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}