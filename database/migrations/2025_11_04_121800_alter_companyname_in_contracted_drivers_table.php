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
        Schema::table('t_ContractedDrivers', function (Blueprint $table) {
            if (Schema::hasColumn('t_ContractedDrivers', 'Company')) {
                $table->dropForeign(['Company']);
                $table->dropColumn('Company');
            }

            $table->foreignId('CompanyID')
                ->nullable()->constrained('t_ThirdParties', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ContractedDrivers', function (Blueprint $table) {
            $table->dropForeign(['CompanyID']);
            $table->dropColumn('CompanyID');

            $table->foreignId('Company')->nullable()->constrained('t_Suppliers', 'Id');
        });
    }
};
