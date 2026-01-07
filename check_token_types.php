<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking tokenable_type values...\n\n";
$tokens = DB::table('t_SYSPersonalAccessTokens')->select('id', 'tokenable_type')->get();
foreach($tokens as $t) {
    echo "ID: {$t->id}, Type: {$t->tokenable_type}\n";
}
