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
        Schema::create('t_PrequalificationApplicationCategories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ApplicationID');
            $table->unsignedBigInteger('CategoryID');
            $table->timestamps();

            $table->foreign('ApplicationID')
                ->references('ApplicationID')
                ->on('t_SupplierPrequalificationApplications')
                ->onDelete('cascade');

            $table->foreign('CategoryID')
                ->references('SupplierCategoryID')
                ->on('t_SupplierCategories')
                ->onDelete('cascade');
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
