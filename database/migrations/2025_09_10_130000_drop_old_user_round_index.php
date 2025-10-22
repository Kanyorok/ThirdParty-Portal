<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Drop the old unique index that's causing conflicts
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes i
                JOIN sys.objects o ON i.object_id = o.object_id
                WHERE i.name = 'uq_user_round' AND o.name = 't_SupplierPrequalificationApplications'
            )
            BEGIN
                DROP INDEX uq_user_round ON dbo.t_SupplierPrequalificationApplications;
            END
        ");
    }

    public function down(): void
    {
        // Recreate the old index if needed for rollback
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->unique(['CreatedBy', 'RoundID'], 'uq_user_round');
        });
    }
};
