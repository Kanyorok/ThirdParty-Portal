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
        Schema::create('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->id('ApplicationID');

            $table->foreignId('SupplierID')->constrained('t_ThirdParties', 'Id')->onDelete('cascade');
            $table->foreignId('RoundID')->constrained('t_PrequalificationRounds', 'RoundID')->onDelete('cascade');
            $table->foreignId('CategoryID')->nullable()->constrained('t_SupplierCategories', 'SupplierCategoryID');

            $table->dateTime('SubmittedOn')->useCurrent();
            $table->string('Status', 50);

            $table->foreignId('CreatedBy')->constrained('t_ThirdPartyUsers', 'Id');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
            $table->dateTime('ModifiedOn')->nullable();
            $table->foreignId('DeletedBy')->nullable()->constrained('t_ThirdPartyUsers', 'Id');
            $table->dateTime('DeletedOn')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            Schema::dropIfExists('t_SupplierPrequalificationApplications');
        });
    }
};
