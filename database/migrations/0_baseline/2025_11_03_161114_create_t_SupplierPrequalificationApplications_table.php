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
        Schema::create('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            $table->bigIncrements('ApplicationID');
            $table->bigInteger('SupplierID');
            $table->bigInteger('RoundID');
            $table->dateTime('SubmittedOn')->useCurrent();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->enum('Status', ['S', 'V', 'P', 'A', 'R'])->default('S');
            $table->text('GeneralComments')->nullable();
            $table->bigInteger('CategoryID')->nullable();

            $table->primary(['ApplicationID'], 'pk__t_suppli__c93a4f79d5e1b88b');
            $table->unique(['RoundID', 'SupplierID', 'CategoryID'], 'uq_round_supplier_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_SupplierPrequalificationApplications');
    }
};
