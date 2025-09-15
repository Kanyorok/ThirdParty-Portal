<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            // add CategoryID column (nullable initially to avoid breaking existing rows)
            $table->unsignedBigInteger('CategoryID')->nullable()->after('RoundID');

            // add FK -> t_SupplierCategories.SupplierCategoryID
            $table->foreign('CategoryID')->references('SupplierCategoryID')->on('t_SupplierCategories');
        });

        // drop old unique index/constraint if it exists (named 'uq_supplier_round' or similar)
        DB::statement("
            IF EXISTS (
                SELECT 1 FROM sys.indexes i
                JOIN sys.objects o ON i.object_id = o.object_id
                WHERE i.name = 'uq_supplier_round' AND o.name = 't_SupplierPrequalificationApplications'
            )
            BEGIN
                DROP INDEX uq_supplier_round ON dbo.t_SupplierPrequalificationApplications;
            END
        ");

        // create the new unique constraint / index on (RoundID, SupplierID, CategoryID)
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->unique(['RoundID', 'SupplierID', 'CategoryID'], 'uq_round_supplier_category');
        });
    }

    public function down(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            // drop new unique
            $table->dropUnique('uq_round_supplier_category');

            // drop foreign and column
            $table->dropForeign(['CategoryID']);
            $table->dropColumn('CategoryID');
        });

        // optional: recreate old supplier+round unique if needed
        // DB::statement("CREATE UNIQUE INDEX uq_supplier_round ON dbo.t_SupplierPrequalificationApplications (SupplierID, RoundID);");
    }
};
