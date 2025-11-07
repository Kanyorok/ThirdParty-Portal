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
        Schema::create('t_BancassuranceCommissionsEarned', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PolicyId');
            $table->bigInteger('ReferralId');
            $table->bigInteger('CommissionRuleId');
            $table->bigInteger('EarnedByType');
            $table->string('EarnedById');
            $table->float('EarnedAmount');
            $table->string('Status');
            $table->date('EarnedDate');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07d97b8c48');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceCommissionsEarned');
    }
};
