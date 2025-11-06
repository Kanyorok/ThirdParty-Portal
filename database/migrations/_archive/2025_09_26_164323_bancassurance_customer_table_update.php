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
            // Drop unique constraints first
            $table->dropUnique('t_bancassurancecustomers_nationalid_unique');
            $table->dropUnique('t_bancassurancecustomers_krapin_unique');
            $table->dropUnique('t_bancassurancecustomers_phonenumber_unique');
            $table->dropUnique('t_bancassurancecustomers_email_unique');

            // Now drop columns
            $table->dropColumn('FullName');
            $table->dropColumn('NationalID');
            $table->dropColumn('KRAPIN');
            $table->dropColumn('PhoneNumber');
            $table->dropColumn('Email');
            $table->dropColumn('Address');

            // Add new foreign key
            $table->foreignId('ThirdPartyId')->constrained('t_ThirdParties','Id');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceCustomers', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['ThirdPartyId']);
            $table->dropColumn('ThirdPartyId');

            // Restore dropped columns (with indexes)
            $table->string('FullName');
            $table->string('NationalID')->unique();
            $table->string('KRAPIN')->unique();
            $table->string('PhoneNumber')->unique();
            $table->string('Email')->unique()->nullable();
            $table->string('Address')->nullable();
        });
    }
};
