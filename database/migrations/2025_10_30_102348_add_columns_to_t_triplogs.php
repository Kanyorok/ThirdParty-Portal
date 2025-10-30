<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {

            $table->foreignId('Status')->nullable()->after('Notes')->constrained('t_CodeDetails', 'ID');
            $table->dropForeign(['TripStatus']);
            $table->dropColumn('TripStatus');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {
            $table->dropForeign(['Status']);
            $table->dropColumn(['Status',
            $table->foreignId('TripStatus')->nullable()->constrained('t_CodeDetails', 'ID'),
            ]);
        });
    }
};
