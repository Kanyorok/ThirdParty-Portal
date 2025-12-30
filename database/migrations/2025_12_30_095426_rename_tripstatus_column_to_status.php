<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_TripLogs', function (Blueprint $table) {
            $table->dropForeign(['TripStatus']);

            $table->dropColumn('TripStatus');

            $table->string('Status')->nullable()->after('Notes');
        });
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
