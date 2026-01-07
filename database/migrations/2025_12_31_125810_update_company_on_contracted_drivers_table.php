<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_ContractedDrivers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('CompanyID');
        });

         Schema::table('t_ContractedDrivers', function (Blueprint $table) {
            $table->foreignId('CompanyID')->nullable()->constrained('t_SupplierMaster', 'Id');
        });
    }

    public function down(): void
    {
        Schema::table('t_ContractedDrivers', function (Blueprint $table) {

            $table->dropForeign('CompanyID');
            $table->dropColumn('CompanyID');
            $table->foreignId('CompanyID')
                ->nullable()->constrained('t_ThirdParties', 'Id');

        });
    }
};
