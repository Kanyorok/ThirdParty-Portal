<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $actorId = DB::table('t_Modules')->whereNotNull('CreatedBy')->value('CreatedBy')
            ?? DB::table('t_Users')->min('Id')
            ?? 1;

        DB::table('t_Modules')
            ->whereIn('ModuleID', [209405, 209415])
            ->update([
                'DeletedOn' => now(),
                'DeletedBy' => $actorId,
                'ModifiedOn' => now(),
                'ModifiedBy' => $actorId,
            ]);
    }

    public function down(): void
    {
        $actorId = DB::table('t_Modules')->whereNotNull('CreatedBy')->value('CreatedBy')
            ?? DB::table('t_Users')->min('Id')
            ?? 1;

        DB::table('t_Modules')
            ->whereIn('ModuleID', [209405, 209415])
            ->update([
                'DeletedOn' => null,
                'DeletedBy' => null,
                'ModifiedOn' => now(),
                'ModifiedBy' => $actorId,
            ]);
    }
};
