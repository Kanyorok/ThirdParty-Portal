<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== t_Employees Structure ===" . PHP_EOL;
$oldCols = DB::select("SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 't_Employees' 
    ORDER BY ORDINAL_POSITION");
foreach ($oldCols as $col) {
    $len = $col->CHARACTER_MAXIMUM_LENGTH ? "({$col->CHARACTER_MAXIMUM_LENGTH})" : "";
    echo "  {$col->COLUMN_NAME} ({$col->DATA_TYPE}{$len})" . PHP_EOL;
}

echo PHP_EOL . "=== t_HREmployees Structure ===" . PHP_EOL;
$newCols = DB::select("SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 't_HREmployees' 
    ORDER BY ORDINAL_POSITION");
foreach ($newCols as $col) {
    $len = $col->CHARACTER_MAXIMUM_LENGTH ? "({$col->CHARACTER_MAXIMUM_LENGTH})" : "";
    echo "  {$col->COLUMN_NAME} ({$col->DATA_TYPE}{$len})" . PHP_EOL;
}

echo PHP_EOL . "=== Row Counts ===" . PHP_EOL;
echo "t_Employees: " . DB::table('t_Employees')->count() . " records" . PHP_EOL;
echo "t_HREmployees: " . DB::table('t_HREmployees')->count() . " records" . PHP_EOL;
