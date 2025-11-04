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
        Schema::create('t_BancassuranceCommissionRules', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RuleName');
            $table->bigInteger('ProductId');
            $table->bigInteger('PolicyTypeId');
            $table->float('CommissionRate');
            $table->float('FixedAmount');
            $table->bigInteger('AppliesTo');
            $table->boolean('IsActive');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec0778888f3a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceCommissionRules');
    }
};
