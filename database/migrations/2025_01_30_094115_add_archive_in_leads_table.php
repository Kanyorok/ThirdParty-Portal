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
        Schema::table('t_Leads', static function (Blueprint $table) {
            $table->dateTime('ArchivedOn')->nullable();
            $table->foreignId('ArchivedBy')->nullable()->constrained('t_Users', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Leads', static function (Blueprint $table) {
            $table->dropColumn('ArchivedOn');
            $table->dropConstrainedForeignId('ArchivedBy');
        });
    }
};
