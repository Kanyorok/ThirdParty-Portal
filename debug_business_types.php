<?php

use App\Models\Core\Approval\CodeDetail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Listing Business Types...\n";
$types = CodeDetail::where('CodeID', 'BusinessType')->whereNull('DeletedOn')->get(['Id', 'Description', 'Value']);

foreach ($types as $t) {
    echo "ID: {$t->Id}, Val: {$t->Value}, Desc: {$t->Description}\n";
}
