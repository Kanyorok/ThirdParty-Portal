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
        Schema::create('t_ContractedDriverLicenses', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('ContractedDriverID')->nullable();
            $table->string('LicenseNumber');
            $table->string('LicenseCategory');
            $table->date('IssueDate');
            $table->date('ExpiryDate');
            $table->string('Notes')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_contra__3214ec0786c27234');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ContractedDriverLicenses');
    }
};
