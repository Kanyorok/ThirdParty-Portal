<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // MSSQL: change DocumentId to NVARCHAR to store UUID (matches DMS Document.DocumentId)
        DB::statement('ALTER TABLE [t_SupplierPreqApplicationDocuments] ALTER COLUMN [DocumentId] NVARCHAR(50) NOT NULL');
    }

    public function down(): void
    {
        // Revert to BIGINT if needed (may fail if non-numeric values exist)
        DB::statement('ALTER TABLE [t_SupplierPreqApplicationDocuments] ALTER COLUMN [DocumentId] BIGINT NOT NULL');
    }
};
