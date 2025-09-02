<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');
$rows = $db->select('SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?', ['t_ThirdParty_SupplierCategory']);
if (count($rows) === 0) {
    echo "NOT_FOUND\n";
} else {
    foreach ($rows as $r) {
        echo $r->TABLE_NAME . "\n";
    }
}
