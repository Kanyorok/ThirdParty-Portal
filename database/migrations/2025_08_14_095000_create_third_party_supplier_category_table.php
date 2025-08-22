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
            $table->foreignId('RoundID')
                ->constrained('t_PrequalificationRounds', 'RoundID')
                ->onDelete('cascade');

            $table->unsignedBigInteger('ThirdPartyID');

            $table->unsignedBigInteger('SupplierCategoryID');

            $table->primary(['RoundID', 'ThirdPartyID', 'SupplierCategoryID']);

            $table->foreign('ThirdPartyID')
                ->references('Id')->on('t_ThirdParties')
                ->onDelete('cascade');

            $table->foreign('SupplierCategoryID')
                ->references('SupplierCategoryID')->on('t_SupplierCategories')
                ->onDelete('cascade');

            $table->timestamp('CreatedOn')->useCurrent();
            $table->timestamp('ModifiedOn')->useCurrent()->nullable();
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
