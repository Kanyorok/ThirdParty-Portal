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
        Schema::create('t_BancassuranceBeneficiaries', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CustomerID');
            $table->string('PolicyID');
            $table->string('FullName');
            $table->string('Relationship');
            $table->string('IDNumber');
            $table->string('Phone');
            $table->string('Email');
            $table->float('PercentageShare');
            $table->boolean('IsPrimary ');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07250396bd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceBeneficiaries');
    }
};
