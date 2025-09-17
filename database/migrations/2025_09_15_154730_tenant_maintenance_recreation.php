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
        Schema::table('t_TenantMaintenance', function (Blueprint $table) {
            // Drop unique constraints first
            $table->dropUnique('t_TenantMaintenance_IDRegistrationNo_unique');
            $table->dropUnique('t_TenantMaintenance_PhoneNumber_unique');
            $table->dropUnique('t_TenantMaintenance_EmailAddress_unique');

            $table->dropColumn([
                'TenantName',
                'IDRegistrationNo',
                'PhoneNumber',
                'EmailAddress',
                'Nationality',
                'PostalAddress',
            ]);

            $table->foreignId('ThirdPartyId')
                ->default(2)
                ->after('TenantType')
                ->constrained('t_ThirdParties', 'Id');

        });
    }

    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('t_TenantMaintenance', function (Blueprint $table) {
        $table->dropForeign(['ThirdPartyId']);
        $table->dropColumn('ThirdPartyId');
        $table->string('TenantName')->nullable();
        $table->string('IDRegistrationNo')->nullable();
        $table->string('PhoneNumber')->nullable();
        $table->string('EmailAddress')->nullable();
        $table->string('Nationality')->nullable();
        $table->string('PostalAddress')->nullable();
    });

    // Recreate unique indexes as filtered (SQL Server supports this)
    DB::statement('CREATE UNIQUE INDEX t_TenantMaintenance_IDRegistrationNo_unique 
                   ON t_TenantMaintenance(IDRegistrationNo) 
                   WHERE IDRegistrationNo IS NOT NULL');

    DB::statement('CREATE UNIQUE INDEX t_TenantMaintenance_PhoneNumber_unique 
                   ON t_TenantMaintenance(PhoneNumber) 
                   WHERE PhoneNumber IS NOT NULL');

    DB::statement('CREATE UNIQUE INDEX t_TenantMaintenance_EmailAddress_unique 
                   ON t_TenantMaintenance(EmailAddress) 
                   WHERE EmailAddress IS NOT NULL');
}

};
