<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "--- FIXING PIVOT TABLE ModifiedBy FK ---\n";

try {
    echo "Dropping old constraint 'fk_tptp_modifiedby_tpu' (points to ThirdPartyUsers)...\n";
    try {
        DB::statement("ALTER TABLE t_ThirdPartyType_ThirdParties DROP CONSTRAINT fk_tptp_modifiedby_tpu");
        echo "Dropped constraint.\n";
    } catch (\Exception $e) {
        echo "Warning dropping constraint: " . $e->getMessage() . "\n";
    }

    echo "Adding new constraint 'fk_tptp_modifiedby_users' (points to t_Users)...\n";
    // NOTE: t_Users id is 'Id' (bigint)
    DB::statement("ALTER TABLE t_ThirdPartyType_ThirdParties ADD CONSTRAINT fk_tptp_modifiedby_users FOREIGN KEY (ModifiedBy) REFERENCES t_Users(Id)");
    echo "SUCCESS: Added correct FK constraint for ModifiedBy.\n";
} catch (\Exception $e) {
    echo "CRITICAL ERROR: " . $e->getMessage() . "\n";
}
