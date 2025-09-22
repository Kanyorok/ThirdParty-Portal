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
            try { $table->dropUnique('t_TenantMaintenance_IDRegistrationNo_unique'); } catch (\Throwable $e) {}
            try { $table->dropUnique('t_TenantMaintenance_PhoneNumber_unique'); } catch (\Throwable $e) {}
            try { $table->dropUnique('t_TenantMaintenance_EmailAddress_unique'); } catch (\Throwable $e) {}

            $table->dropColumn([
                'TenantName',
                'IDRegistrationNo',
                'PhoneNumber',
                'EmailAddress',
                'Nationality',
                'PostalAddress',
            ]);

            if (!Schema::hasColumn('t_TenantMaintenance', 'ThirdPartyId')) {
                $table->unsignedBigInteger('ThirdPartyId')->after('TenantType')->nullable();
            }

        });

        // Backfill nullable foreign key with an existing third party if available
        if (!DB::table('t_ThirdParties')->where('Id', 2)->exists()) {
            $fallbackId = DB::table('t_ThirdParties')->value('Id');
            DB::table('t_TenantMaintenance')->whereNull('ThirdPartyId')->update(['ThirdPartyId' => $fallbackId]);
        } else {
            DB::table('t_TenantMaintenance')->whereNull('ThirdPartyId')->update(['ThirdPartyId' => 2]);
        }

        // Add the foreign key constraint safely
        Schema::table('t_TenantMaintenance', function (Blueprint $table) {
            $table->foreign('ThirdPartyId')->references('Id')->on('t_ThirdParties');
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
