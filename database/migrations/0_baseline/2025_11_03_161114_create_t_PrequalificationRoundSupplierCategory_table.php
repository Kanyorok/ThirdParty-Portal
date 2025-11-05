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
        Schema::create('t_PrequalificationRoundSupplierCategory', function (Blueprint $table) {
            $table->bigInteger('RoundID');
            $table->bigInteger('ThirdPartyID');
            $table->bigInteger('SupplierCategoryID');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->dateTime('ModifiedOn')->nullable()->useCurrent();

            $table->primary(['RoundID', 'ThirdPartyID', 'SupplierCategoryID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PrequalificationRoundSupplierCategory');
    }
};
