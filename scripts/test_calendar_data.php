<?php

use App\Http\Controllers\HR\LeaveRequestController;
use Illuminate\Http\Request;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Simulate Request
$request = Request::create('/hr/leave/calendar/data', 'GET', [
    'start' => '2025-09-28',
    'end' => '2025-11-08'
]);

$controller = new LeaveRequestController();
$response = $controller->calendarData($request);

$data = $response->getData(true);

echo "Events Count: " . count($data['events']) . "\n";
echo "Daily Counts Keys: " . count($data['daily_counts']) . "\n";
echo "Stats Keys: " . count($data['stats']) . "\n";

echo "Dates with leaves:\n";
foreach ($data['daily_counts'] as $date => $info) {
    if ($info['count'] > 0) {
        echo "$date: " . $info['count'] . " employees\n";
    }
}

