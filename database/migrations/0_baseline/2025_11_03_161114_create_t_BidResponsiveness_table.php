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
        Schema::create('t_BidResponsiveness', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TenderSupplierID');
            $table->boolean('SubmittedTimely');
            $table->boolean('HasMandatoryDocuments');
            $table->boolean('IsEligible');
            $table->boolean('IsResponsive');
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bidres__3214ec07c953d942');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BidResponsiveness');
    }
};
