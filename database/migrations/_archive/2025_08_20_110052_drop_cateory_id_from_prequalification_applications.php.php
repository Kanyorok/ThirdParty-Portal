<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->dropForeign(['CategoryID']);
            $table->dropColumn('CategoryID');
        });
    }

    public function down(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->unsignedBigInteger('CategoryID')->nullable()->after('RoundID');
            $table->foreign('CategoryID')->references('SupplierCategoryID')->on('t_SupplierCategories');
        });
    }
};
