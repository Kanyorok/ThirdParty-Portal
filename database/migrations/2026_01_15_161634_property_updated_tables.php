<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            $table->dropColumn(['ReportedBy']);
            $table->foreignId('ReportedBy')->nullable()->constrained('t_ThirdParties', 'Id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            $table->dropForeign(['ReportedBy']);
            $table->string('ReportedBy')->change();
        });
    }
};
