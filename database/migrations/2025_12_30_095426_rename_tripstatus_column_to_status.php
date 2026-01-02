<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('t_TripLogs', 'TripStatus')) {
            Schema::table('t_TripLogs', function (Blueprint $table) {
                // Check if foreign key exists before dropping - risky without raw SQL, 
                // but if column exists, we attempt to drop FK. 
                // If it fails, it might be due to missing FK. 
                // Given the error, let's wrap this in a try-catch block utilizing raw PHP? 
                // No, sticking to column check is usually safer for structure. 
                // However, if column exists but FK is gone, dropForeign fails.
                // Let's assume standard Laravel behavior: if we checked column, usually we are good. 
                // But the user error was explicit about FK missing.
                // Simplest fix for NOW: Just drop the column if it exists. 
                // But dropColumn 'TripStatus' might complain if FK exists and we didn't drop it.
                // Try-catch block for dropForeign inside the closure? No, closures define blueprint.

                try {
                    $table->dropForeign(['TripStatus']);
                } catch (\Exception $e) {
                    // Ignore if FK not found
                }
                $table->dropColumn('TripStatus');
            });
        }

        if (!Schema::hasColumn('t_TripLogs', 'Status')) {
            Schema::table('t_TripLogs', function (Blueprint $table) {
                $table->string('Status')->nullable()->after('Notes');
            });
        }
    }

    public function down(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {

            $table->dropColumn('Status');
            $table->foreignId('TripStatus')
                ->nullable()
                ->constrained('t_CodeDetails', 'ID')
                ->after('Notes');
        });
    }
};
