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
        Schema::table('t_ContractedDrivers', function (Blueprint $table) {
            $table->dropColumn(['CompanyName', 'LicenseNumber']);
            $table->foreignId('Company')->nullable()->constrained('t_Suppliers', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ContractedDrivers', function (Blueprint $table) {
            $table->dropForeign(['Company']);
            $table->dropColumn('Company');
            $table->string('CompanyName')->nullable();
            $table->string('LicenseNumber')->nullable();
        });
    }
};
