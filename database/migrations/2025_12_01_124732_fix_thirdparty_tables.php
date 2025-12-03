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
        DB::table('t_ScheduleLease')->delete();
        DB::table('t_LeaseCreation')->delete();
        DB::table('t_TenantMaintenance')->delete();

        DB::table('t_BancassuranceCustomers')->delete();

        DB::table('t_ThirdParties')->delete();
        DB::table('t_ThirdPartyType_ThirdParties')->delete();
        DB::table('t_Suppliers')->delete();
        DB::table('t_ThirdPartiesBankDetails')->delete();
        DB::table('t_SupplierPrequalificationApplications')->delete();
        DB::table('t_ThirdPartyUsers')->delete();
        DB::table('t_ThirdPartyTypes')->delete();

        Schema::create('t_SupplierMaster', static function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ThirdPartyId')->constrained('t_ThirdParties', 'Id');
            $table->string('SupplierID', 100)->unique();
            $table->char('ApprovalStatus', 1)->comment('CodeID: ThirdPartyApprovalStatusEnum');
            $table->boolean('IsPrequalified')->default(false);
            $table->jsonb('Extra')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });


        Schema::table('t_ThirdParties', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('CountryId');//dropped because of previous created

            $table->string('TradingName')->nullable()->change();
            $table->string('ThirdPartyName')->nullable(false)->change();
            $table->dropUnique(['RegistrationNumber']);

            $table->jsonb('Extra')->nullable();
            $table->foreignId('ImageId')->nullable()->constrained('t_Images', 'ImageID');
            $table->foreignId('LocationId')->constrained('t_Localities', 'ID');
            $table->dropColumn(['BusinessType', 'IDNumber', 'Country', 'ApprovalStatus', 'Status', 'ThirdPartyType', 'PassportNo', 'IsPrequalified', 'CategoryId', 'CreatedOn', 'DeletedOn', 'ModifiedOn']);
            $table->dropConstrainedForeignId('CreatedBy');
            $table->dropConstrainedForeignId('ModifiedBy');
            $table->dropConstrainedForeignId('DeletedBy');
        });

        Schema::table('t_ThirdParties', static function (Blueprint $table) {
            $table->foreignId('CountryId')->constrained('t_Countries', 'Id');
            $table->string('RegistrationNumber')->nullable(false)->change();

            $table->unique(['CountryId', 'TaxPIN']);

            $table->unique(['CountryId', 'RegistrationNumber']);
            $table->foreignId('Status')->comment('CodeID: ThirdPartyStatus')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('BusinessType')->comment('CodeID: BusinessType')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_Suppliers', static function (Blueprint $table) {
            $table->dropIndex('uq_t_suppliers_round_tp_cat');
            $table->dropConstrainedForeignId('ThirdPartyId');

            $table->foreignId('SupplierMasterId')->constrained('t_SupplierMaster', 'Id');
            $table->unique(['SupplierMasterId', 'RoundID', 'CategoryId']);
        });


        Schema::table('t_ThirdPartyTypes', static function (Blueprint $table) {
            $table->dropIndex(['Type']);
            $table->dropUnique(['Code']);
            $table->dropConstrainedForeignId('Type');
            $table->string('Description')->nullable(false)->change();
            $table->string('Code')->unique()->nullable(false)->change();
        });

        Schema::table('t_ThirdPartyUsers', static function (Blueprint $table) {
            $table->jsonb('Extra')->nullable();
            $table->dropColumn('Gender');
            $table->dropConstrainedForeignId('CreatedBy');
            $table->dropConstrainedForeignId('ModifiedBy');
            $table->dropConstrainedForeignId('DeletedBy');
        });
        Schema::table('t_ThirdPartyUsers', static function (Blueprint $table) {
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->foreignId('Gender')->comment('CodeID: Gender')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
        });

        Schema::table('t_ThirdPartiesBankDetails', static function (Blueprint $table) {
            $table->dropColumn(['BankName', 'Branch', 'SwiftCode']);
            $table->jsonb('Extra')->nullable();
            $table->foreignId('BranchID')->constrained('t_BankBranches', 'BranchID');
        });

        Schema::table('t_SupplierPrequalificationApplications', static function (Blueprint $table) {
            $table->dropForeign('t_supplierprequalificationapplications_supplierid_foreign');
            $table->foreign('SupplierId')->references('Id')->on('t_SupplierMaster')->onDelete('cascade');
        });

        Schema::table('t_ThirdPartyType_ThirdParties', static function (Blueprint $table) {
            $table->string('PartyType', 100)->comment('Supplier, Customer, Tenant');
            $table->string('PartyID', 100);
            $table->index(['PartyType', 'PartyID']);
        });

        if (\App\Models\Auth\User::query()->exists()) {
            Artisan::call('db:seed', [
                '--class' => 'ThirdPartyTypesSeeder',
                '--force' => true
            ]);
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('t_ScheduleLease')->delete();
        DB::table('t_LeaseCreation')->delete();
        DB::table('t_TenantMaintenance')->delete();

        DB::table('t_BancassuranceCustomers')->delete();

        DB::table('t_SupplierMaster')->delete();

        DB::table('t_ThirdParties')->delete();
        DB::table('t_ThirdPartyType_ThirdParties')->delete();
        DB::table('t_Suppliers')->delete();
        DB::table('t_ThirdPartiesBankDetails')->delete();
        DB::table('t_SupplierPrequalificationApplications')->delete();
        DB::table('t_ThirdPartyUsers')->delete();
        DB::table('t_ThirdPartyTypes')->delete();

        Schema::table('t_ThirdPartyType_ThirdParties', static function (Blueprint $table) {
            $table->dropIndex(['PartyType', 'PartyID']);
            $table->dropColumn(['PartyType', 'PartyID']);
        });

        Schema::table('t_SupplierPrequalificationApplications', static function (Blueprint $table) {
            $table->dropForeign('t_supplierprequalificationapplications_supplierid_foreign');
            $table->foreign('SupplierId')->references('Id')->on('t_ThirdParties')->onDelete('cascade');
        });

        Schema::table('t_ThirdPartiesBankDetails', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BranchID');
            $table->dropColumn('Extra');
            $table->string('BankName');
            $table->string('Branch');
            $table->string('SwiftCode');
        });

        Schema::table('t_ThirdPartyUsers', static function (Blueprint $table) {
            $table->dropColumn('Extra');
            $table->dropConstrainedForeignId('Gender');
            $table->dropConstrainedForeignId('CreatedBy');
            $table->dropConstrainedForeignId('ModifiedBy');
            $table->dropConstrainedForeignId('DeletedBy');
        });

        Schema::table('t_ThirdPartyUsers', static function (Blueprint $table) {
            $table->string('Gender')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_ThirdPartyUsers', 'Id');
            $table->foreignId('ModifiedBy')->constrained('t_ThirdPartyUsers', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
        });

        Schema::table('t_ThirdPartyTypes', static function (Blueprint $table) {
            $table->dropUnique(['Code']);
            $table->foreignId('Type')->index()->constrained('t_CategoryMaster', 'Id');
            $table->string('Description')->nullable()->change();
            $table->string('Code')->unique()->nullable()->change();
        });

        Schema::table('t_ThirdParties', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('ImageId');
            $table->dropConstrainedForeignId('LocationId');
            $table->dropConstrainedForeignId('Status');
            $table->dropConstrainedForeignId('BusinessType');
            $table->dropColumn(['Extra']);
            $table->dropConstrainedForeignId('CreatedBy');
            $table->dropConstrainedForeignId('DeletedBy');

            $table->dropUnique(['CountryId', 'RegistrationNumber']);
            $table->dropUnique(['CountryId', 'TaxPIN']);
        });

        Schema::table('t_ThirdParties', static function (Blueprint $table) {
            $table->string('ThirdPartyName')->nullable()->change();
            $table->string('RegistrationNumber')->nullable()->unique()->change();
            $table->string('TradingName')->nullable()->change();
            $table->string('Country')->nullable();
            $table->string('BusinessType')->nullable();
            $table->string('IDNumber')->nullable();
            $table->string('ApprovalStatus')->nullable();
            $table->string('Status')->nullable();
            $table->string('ThirdPartyType')->nullable();
            $table->string('PassportNo')->nullable();
            $table->string('CategoryId')->nullable();
            $table->boolean('IsPrequalified')->default(false);

            $table->foreignId('CreatedBy')->constrained('t_ThirdPartyUsers', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
        });

        Schema::table('t_Suppliers', static function (Blueprint $table) {
            $table->dropUnique(['SupplierMasterId', 'RoundID', 'CategoryId']);
            $table->dropConstrainedForeignId('SupplierMasterId');
            $table->foreignId('ThirdPartyId')->constrained('t_ThirdParties', 'Id');
            $table->index(['ThirdPartyId', 'RoundID', 'CategoryId'], 'uq_t_suppliers_round_tp_cat');
        });

        Schema::dropIfExists('t_SupplierMaster');
    }
};
