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
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            // 1. Drop unique constraints via raw SQL (SQL Server safe)
        });

//        DB::statement('DROP INDEX t_Bancassu_NationalID_unique ON t_BancassuranceCustomers');
//        DB::statement('DROP INDEX t_Bancassu_KRAPIN_unique ON t_BancassuranceCustomers');
//        DB::statement('DROP INDEX t_Bancassu_PhoneNumber_unique ON t_BancassuranceCustomers');
//        DB::statement('DROP INDEX t_Bancassu_Email_unique ON t_BancassuranceCustomers');

        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            // 2. Drop old customer info columns
//            $table->dropColumn([
//                'FullName',
//                'NationalID',
//                'KRAPIN',
//                'PhoneNumber',
//                'Email',
//                'Address'
//            ]);

            // 3. Add ThirdPartyId
//            $table->foreignId('ThirdPartyId')
//                ->nullable()
//                ->constrained('t_ThirdParties', 'Id');
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            // Drop foreign key + column
//            $table->dropForeign(['ThirdPartyId']);
//            $table->dropColumn('ThirdPartyId');
//
//            // Restore old columns as nullable (to avoid rollback failure on non-empty table)
//            $table->string('FullName')->nullable();
//            $table->string('NationalID')->nullable();
//            $table->string('KRAPIN')->nullable();
//            $table->string('PhoneNumber')->nullable();
//            $table->string('Email')->nullable();
//            $table->string('Address')->nullable();
        });
    }
};
