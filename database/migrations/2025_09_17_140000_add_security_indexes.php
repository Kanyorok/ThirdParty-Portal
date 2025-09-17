<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create indexes if not exists (SQL Server / MySQL friendly via raw statements)
        try {
            DB::statement('CREATE INDEX IX_ThirdPartyUsers_IsActive_IsApproved ON t_ThirdPartyUsers (IsActive, ThirdPartyId)');
        } catch (\Throwable $e) {}
        try {
            DB::statement('CREATE INDEX IX_PersonalAccessTokens_Tokenable ON personal_access_tokens (tokenable_id, tokenable_type)');
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        try { DB::statement('DROP INDEX IX_ThirdPartyUsers_IsActive_IsApproved ON t_ThirdPartyUsers'); } catch (\Throwable $e) {}
        try {
            // SQL Server syntax requires table-qualified drop
            DB::statement('DROP INDEX IX_PersonalAccessTokens_Tokenable ON personal_access_tokens');
        } catch (\Throwable $e) {}
    }
};
