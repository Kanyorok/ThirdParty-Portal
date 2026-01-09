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
        Schema::table('t_AssignRequest', function (Blueprint $table) {

            $table->dropForeign(['PrequalifiedVendor']);

            $table->foreign('PrequalifiedVendor')
                ->references('Id')
                ->on('t_SupplierMaster');
        });
    }

    public function down(): void
    {
        Schema::table('t_AssignRequest', function (Blueprint $table) {

            $table->dropForeign(['PrequalifiedVendor']);

            $table->foreign('PrequalifiedVendor')
                ->references('Id')
                ->on('t_Suppliers');
        });
    }

};
