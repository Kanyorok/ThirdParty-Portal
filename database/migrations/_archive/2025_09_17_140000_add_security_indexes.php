<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Create composite index on tokenable for the new table name
        DB::statement('CREATE INDEX IX_SYSPersonalAccessTokens_Tokenable ON t_SYSPersonalAccessTokens (tokenable_id, tokenable_type)');
    }

    public function down(): void
    {
        // Drop the index if it exists
        DB::statement('DROP INDEX IX_SYSPersonalAccessTokens_Tokenable ON t_SYSPersonalAccessTokens');
    }
};
