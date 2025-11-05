<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Add new binary column to store the raw encrypted envelope bytes
        DB::statement("ALTER TABLE dbo.t_BidSubmissions ADD EncryptionEnvelope VARBINARY(MAX) NULL;");

        // Backfill from existing base64-encoded NVARCHAR EncryptionKey if present
        // This uses SQL Server's XML base64 decoder to convert base64 text to varbinary
        $sql = "UPDATE T SET EncryptionEnvelope = CAST('' AS XML).value('xs:base64Binary(sql:column(\"T.EncryptionKey\"))', 'varbinary(max)') FROM dbo.t_BidSubmissions AS T WHERE T.EncryptionKey IS NOT NULL AND T.EncryptionEnvelope IS NULL;";
        DB::statement($sql);
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dbo.t_BidSubmissions DROP COLUMN EncryptionEnvelope;");
    }
};
