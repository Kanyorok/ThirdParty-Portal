<?php

use App\Http\Controllers\HR\LeaveRequestController;
use Illuminate\Http\Request;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Request::capture());

// Simulate Request for October 2025
// FullCalendar usually fetches a range surrounding the month.
$request = Request::create('/hr/leave/calendar/data', 'GET', [
    'start' => '2025-09-28',
    'end' => '2025-11-08'
]);

$controller = new LeaveRequestController();
$response = $controller->calendarData($request);
$data = $response->getData(true);

echo "--- Debugging Data for 2025-10-29 ---\n";

if (isset($data['daily_counts']['2025-10-29'])) {
    $dayData = $data['daily_counts']['2025-10-29'];
    echo "Count: " . $dayData['count'] . "\n";
    echo "Employees:\n";
    foreach ($dayData['employees'] as $emp) {
        echo "- " . $emp['name'] . " (" . $emp['type'] . ")\n";
    }
} else {
    echo "No data found for 2025-10-29 in daily_counts.\n";
}

echo "\n--- Checking Specific Employee 'Diamond' ---\n";
// Check raw events for Diamond
$found = false;
foreach ($data['events'] as $event) {
    if (stripos($event['title'], 'Diamond') !== false) {
        echo "Found Event: " . json_encode($event, JSON_PRETTY_PRINT) . "\n";
        echo "Event Start Raw: " . $event['start'] . "\n";
        $found = true;
    }
}

if (!$found) {
    echo "No event found with title containing 'Diamond'.\n";
}

echo "\n--- Checking Stats Summary ---\n";
echo json_encode($data['stats'], JSON_PRETTY_PRINT) . "\n";
