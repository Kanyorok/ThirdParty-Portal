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
        Schema::create('t_FinanceTaxJurisdiction', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('JurisdictionName', 100)->unique();
            $table->bigInteger('Currency');
            $table->string('TaxAuthority');
            $table->boolean('Status')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('CountryID')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec0743534881');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceTaxJurisdiction');
    }
};
