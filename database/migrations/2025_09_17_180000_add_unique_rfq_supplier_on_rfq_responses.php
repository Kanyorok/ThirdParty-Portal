<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add a filtered UNIQUE index on (RFQId, SupplierId) where not soft-deleted
        try {
            DB::statement(<<<SQL
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'uq_t_rfqresponse_rfq_supplier' AND object_id = OBJECT_ID('t_RFQResponse'))
BEGIN
    CREATE UNIQUE INDEX uq_t_rfqresponse_rfq_supplier ON dbo.t_RFQResponse (RFQId, SupplierId) WHERE DeletedOn IS NULL;
END
SQL);
        } catch (\Throwable $e) {
            // Fallback: create non-unique composite index if filtered unique is not supported
            try {
                DB::statement(<<<SQL
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'ix_t_rfqresponse_rfq_supplier' AND object_id = OBJECT_ID('t_RFQResponse'))
BEGIN
    CREATE INDEX ix_t_rfqresponse_rfq_supplier ON dbo.t_RFQResponse (RFQId, SupplierId);
END
SQL);
            } catch (\Throwable $ignored) {}
        }
    }

    public function down(): void
    {
        try {
            DB::statement(<<<SQL
IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'uq_t_rfqresponse_rfq_supplier' AND object_id = OBJECT_ID('t_RFQResponse'))
BEGIN
    DROP INDEX uq_t_rfqresponse_rfq_supplier ON dbo.t_RFQResponse;
END
SQL);
        } catch (\Throwable $e) {}

        try {
            DB::statement(<<<SQL
IF EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'ix_t_rfqresponse_rfq_supplier' AND object_id = OBJECT_ID('t_RFQResponse'))
BEGIN
    DROP INDEX ix_t_rfqresponse_rfq_supplier ON dbo.t_RFQResponse;
END
SQL);
        } catch (\Throwable $e) {}
    }
};


