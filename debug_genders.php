<?php

use App\Models\Core\Approval\CodeDetail;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "Listing Genders...\n";
$genders = CodeDetail::where('CodeID', 'Gender')->whereNull('DeletedOn')->get(['Id', 'Description', 'Value']);

foreach ($genders as $g) {
    echo "ID: {$g->Id}, Val: {$g->Value}, Desc: {$g->Description}\n";
}
