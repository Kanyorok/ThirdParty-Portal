<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // NVARCHAR(MAX) to store Laravel encrypted envelope and JSON metadata safely
        DB::statement("ALTER TABLE dbo.t_BidSubmissions ALTER COLUMN EncryptionKey NVARCHAR(MAX) NULL;");
        DB::statement("ALTER TABLE dbo.t_BidSubmissions ALTER COLUMN EncryptedDocuments NVARCHAR(MAX) NULL;");
    }

    public function down(): void
    {
        // If you know prior sizes, set them back here. Leaving as no-op to avoid truncation on rollback.
        // Example:
        DB::statement("ALTER TABLE dbo.t_BidSubmissions ALTER COLUMN EncryptionKey NVARCHAR(255) NULL;");
        DB::statement("ALTER TABLE dbo.t_BidSubmissions ALTER COLUMN EncryptedDocuments NVARCHAR(4000) NULL;");
    }
};


