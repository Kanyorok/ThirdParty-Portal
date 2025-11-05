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
        Schema::create('t_BankBranches', function (Blueprint $table) {
            $table->bigIncrements('BranchID');
            $table->bigInteger('BankID');
            $table->string('BranchName', 200);
            $table->string('BranchCode', 50)->nullable();
            $table->string('Address1', 200)->nullable();
            $table->string('Address2', 200)->nullable();
            $table->bigInteger('CityID')->nullable();
            $table->bigInteger('CountryID')->nullable();
            $table->string('ZipCode', 20)->nullable();
            $table->string('Phone', 50)->nullable();
            $table->string('EmailID', 150)->nullable();
            $table->boolean('IsActive')->default(true);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['BranchID'], 'pk__t_bankbr__a1682fa5732b5d6c');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BankBranches');
    }
};
