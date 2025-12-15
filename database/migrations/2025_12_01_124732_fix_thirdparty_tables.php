<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;


return new class extends Migration {

    private function dropFkIfExists(string $table, string $column)
    {
        $fk = DB::select("
            SELECT fk.name AS fk_name
            FROM sys.foreign_keys fk
            JOIN sys.foreign_key_columns fkc ON fk.object_id = fkc.constraint_object_id
            JOIN sys.columns c ON fkc.parent_object_id = c.object_id AND fkc.parent_column_id = c.column_id
            JOIN sys.tables t ON fk.parent_object_id = t.object_id
            WHERE t.name = ? AND c.name = ?
        ", [$table, $column]);

        if (!empty($fk)) {
            $name = $fk[0]->fk_name;
            DB::statement("ALTER TABLE [$table] DROP CONSTRAINT [$name]");
        }
    }

    private function dropIndexIfExists(string $table, string $index)
    {
        $exists = DB::select("
            SELECT name FROM sys.indexes
            WHERE name = ? AND object_id = OBJECT_ID(?)
        ", [$index, $table]);

        if (!empty($exists)) {
            Schema::table($table, function (Blueprint $t) use ($index) {
                $t->dropIndex($index);
            });
        }
    }

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

        Schema::create('t_SupplierMaster', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('ThirdPartyId')->constrained('t_ThirdParties', 'Id');
            $table->string('SupplierID', 100)->unique();
            $table->char('ApprovalStatus', 1);
            $table->boolean('IsPrequalified')->default(false);
            $table->jsonb('Extra')->nullable();
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_ThirdParties', function (Blueprint $table) {});

        $this->dropFkIfExists('t_ThirdParties', 'CountryId');
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->dropColumn('CountryId');
        });

        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->string('TradingName')->nullable()->change();
            $table->string('ThirdPartyName')->nullable(false)->change();
            $table->dropUnique(['RegistrationNumber']);

            $table->jsonb('Extra')->nullable();

            $table->foreignId('ImageId')->nullable()->constrained('t_Images', 'ImageID');
            $table->foreignId('LocationId')->constrained('t_Localities', 'ID');

            $table->dropColumn([
                'BusinessType',
                'IDNumber',
                'Country',
                'ApprovalStatus',
                'Status',
                'ThirdPartyType',
                'PassportNo',
                'IsPrequalified',
                'CategoryId',
                'CreatedOn',
                'DeletedOn',
                'ModifiedOn'
            ]);
        });

        foreach (['CreatedBy', 'ModifiedBy', 'DeletedBy'] as $col) {
            $this->dropFkIfExists('t_ThirdParties', $col);
            if (Schema::hasColumn('t_ThirdParties', $col)) {
                Schema::table('t_ThirdParties', fn(Blueprint $t) => $t->dropColumn($col));
            }
        }

        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->foreignId('CountryId')->constrained('t_Countries', 'Id');
            $table->string('RegistrationNumber')->nullable(false)->change();

            $table->unique(['CountryId', 'TaxPIN']);
            $table->unique(['CountryId', 'RegistrationNumber']);

            $table->foreignId('Status')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('BusinessType')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');

            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');

            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        $this->dropIndexIfExists('t_Suppliers', 'uq_t_suppliers_round_tp_cat');
        $this->dropFkIfExists('t_Suppliers', 'ThirdPartyId');

        if (Schema::hasColumn('t_Suppliers', 'ThirdPartyId')) {
            Schema::table('t_Suppliers', fn(Blueprint $t) => $t->dropColumn('ThirdPartyId'));
        }

        Schema::table('t_Suppliers', function (Blueprint $table) {});

        Schema::table('t_Suppliers', function (Blueprint $table) {
            $table->foreignId('SupplierMasterId')->constrained('t_SupplierMaster', 'Id');
            $table->unique(['SupplierMasterId', 'RoundID', 'CategoryId']);
        });

        $this->dropIndexIfExists('t_ThirdPartyTypes', 't_ThirdPartyTypes_Type_index');

        Schema::table('t_ThirdPartyTypes', function (Blueprint $table) {});

        Schema::table('t_ThirdPartyTypes', function (Blueprint $table) {
            $table->dropUnique(['Code']);
        });

        $this->dropFkIfExists('t_ThirdPartyTypes', 'Type');
        if (Schema::hasColumn('t_ThirdPartyTypes', 'Type')) {
            Schema::table('t_ThirdPartyTypes', fn(Blueprint $t) => $t->dropColumn('Type'));
        }

        Schema::table('t_ThirdPartyTypes', function (Blueprint $table) {});

        $this->dropFkIfExists('t_ThirdPartyTypes', 'Type');
        if (Schema::hasColumn('t_ThirdPartyTypes', 'Type')) {
            Schema::table('t_ThirdPartyTypes', fn(Blueprint $t) => $t->dropColumn('Type'));
        }

        Schema::table('t_ThirdPartyTypes', function (Blueprint $table) {
            $table->string('Description')->nullable(false)->change();
            $table->string('Code')->unique()->nullable(false)->change();
        });

        foreach (['CreatedBy', 'ModifiedBy', 'DeletedBy'] as $col) {
            $this->dropFkIfExists('t_ThirdPartyUsers', $col);
        }

        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->jsonb('Extra')->nullable();
            $table->dropColumn(['Gender', 'CreatedBy', 'ModifiedBy', 'DeletedBy']);
        });

        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->foreignId('Gender')->comment('CodeID: Gender')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
        });

        Schema::table('t_ThirdPartiesBankDetails', function (Blueprint $table) {
            $table->dropColumn(['BankName', 'Branch', 'SwiftCode']);
            $table->jsonb('Extra')->nullable();
            $table->foreignId('BranchID')->constrained('t_BankBranches', 'BranchID');
        });

        $this->dropFkIfExists('t_SupplierPrequalificationApplications', 'SupplierId');

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->foreign('SupplierId')
                ->references('Id')
                ->on('t_SupplierMaster')
                ->onDelete('cascade');
        });

        Schema::table('t_ThirdPartyType_ThirdParties', function (Blueprint $table) {
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

    public function down(): void {}
};
