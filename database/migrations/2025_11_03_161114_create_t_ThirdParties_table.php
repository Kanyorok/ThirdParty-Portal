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
        Schema::create('t_ThirdParties', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ThirdPartyName')->nullable();
            $table->string('TradingName')->nullable();
            $table->string('BusinessType')->nullable();
            $table->string('RegistrationNumber')->nullable()->unique();
            $table->string('TaxPIN')->nullable();
            $table->string('VATNumber')->nullable();
            $table->string('Country')->nullable();
            $table->string('PhysicalAddress')->nullable();
            $table->string('Email')->nullable()->unique();
            $table->string('Phone')->nullable();
            $table->string('Website')->nullable();
            $table->string('ApprovalStatus')->nullable();
            $table->string('Status')->nullable();
            $table->string('ThirdPartyType')->nullable();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('CreatedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->integer('CategoryId')->nullable();
            $table->boolean('IsPrequalified')->default(false);
            $table->bigInteger('CountryId')->nullable();

            $table->primary(['Id'], 'pk__t_thirdp__3214ec074d26730b');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ThirdParties');
    }
};
