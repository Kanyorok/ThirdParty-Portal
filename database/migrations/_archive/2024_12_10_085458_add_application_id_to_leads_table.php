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
        Schema::table('t_Leads', function (Blueprint $table) {
            $table->string('ApplicationID')->nullable()->after('JobTitle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Leads', function (Blueprint $table) {
            $table->dropColumn('ApplicationID');
        });
    }
};
