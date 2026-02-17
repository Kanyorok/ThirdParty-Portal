<?php

use Illuminate\Support\Facades\DB;
use App\Models\HR\AttendanceDevice;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$devices = [
    [
        'DeviceCode' => 'DEV-001',
        'Name' => 'Main Gate Entrance',
        'Channel' => 'Biometric',
        'AllowedIPs' => '192.168.1.10',
        'AllowedLocations' => 'Head Office - Main Gate',
        'IsActive' => true,
    ],
    [
        'DeviceCode' => 'DEV-002',
        'Name' => 'Back Office Exit',
        'Channel' => 'Biometric',
        'AllowedIPs' => '192.168.1.11',
        'AllowedLocations' => 'Head Office - Back Gate',
        'IsActive' => true,
    ],
    [
        'DeviceCode' => 'DEV-003',
        'Name' => 'Production Floor A',
        'Channel' => 'RFID',
        'AllowedIPs' => '192.168.1.20',
        'AllowedLocations' => 'Factory - Floor A',
        'IsActive' => true,
    ],
    [
        'DeviceCode' => 'DEV-004',
        'Name' => 'Production Floor B',
        'Channel' => 'RFID',
        'AllowedIPs' => '192.168.1.21',
        'AllowedLocations' => 'Factory - Floor B',
        'IsActive' => true,
    ],
    [
        'DeviceCode' => 'DEV-005',
        'Name' => 'Sales Dept Scanner',
        'Channel' => 'Biometric',
        'AllowedIPs' => '10.0.0.5',
        'AllowedLocations' => 'Sales Office',
        'IsActive' => true,
    ],
    [
        'DeviceCode' => 'DEV-006',
        'Name' => 'Warehouse Entry',
        'Channel' => 'Face Recognition',
        'AllowedIPs' => '10.0.0.8',
        'AllowedLocations' => 'Warehouse',
        'IsActive' => false, // Inactive device example
    ],
    [
        'DeviceCode' => 'APP-MOBILE-01',
        'Name' => 'Mobile Field App',
        'Channel' => 'GPS Mobile',
        'AllowedIPs' => '*',
        'AllowedLocations' => 'Field - GPS Verified',
        'IsActive' => true,
    ]
];

echo "Adding dummy devices...\n";

foreach ($devices as $d) {
    // Check if device code exists to avoid duplicates if run multiple times
    $exists = AttendanceDevice::where('DeviceCode', $d['DeviceCode'])->exists();
    if (!$exists) {
        AttendanceDevice::create(array_merge($d, [
            'CreatedBy' => 1, // Assuming user ID 1 exists (admin usually)
            'CreatedOn' => now(),
            'ModifiedBy' => 1,
            'ModifiedOn' => now(),
        ]));
        echo "Added: " . $d['Name'] . "\n";
    } else {
        echo "Skipped (Exists): " . $d['Name'] . "\n";
    }
}

echo "Done.\n";
