<?php

use Illuminate\Support\Facades\DB;
use App\Models\HR\AttendanceDaily;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing negative minutes in daily summary...\n";

// Update all records where LateMinutes is negative
$affectedLate = DB::table('t_HRAttendanceDaily')
    ->where('LateMinutes', '<', 0)
    ->update(['LateMinutes' => DB::raw('ABS(LateMinutes)')]);

echo "Fixed $affectedLate records with negative LateMinutes.\n";

// Update all records where EarlyExitMinutes is negative
$affectedEarly = DB::table('t_HRAttendanceDaily')
    ->where('EarlyExitMinutes', '<', 0)
    ->update(['EarlyExitMinutes' => DB::raw('ABS(EarlyExitMinutes)')]);

echo "Fixed $affectedEarly records with negative EarlyExitMinutes.\n";

echo "Done.\n";
