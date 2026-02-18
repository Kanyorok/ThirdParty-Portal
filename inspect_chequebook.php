<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$columns = DB::select("SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 't_ChequeBook'");

foreach ($columns as $col) {
    echo $col->COLUMN_NAME . ": " . $col->DATA_TYPE;
    if ($col->CHARACTER_MAXIMUM_LENGTH) {
        echo "(" . $col->CHARACTER_MAXIMUM_LENGTH . ")";
    }
    echo "\n";
}
