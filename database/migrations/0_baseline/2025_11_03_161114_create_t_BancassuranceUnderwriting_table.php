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
        Schema::create('t_BancassuranceUnderwriting', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('PolicyId');
            $table->date('FeedbackDate');
            $table->integer('RiskScore');
            $table->bigInteger('Decision');
            $table->string('Comments');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bancas__3214ec07e1072988');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BancassuranceUnderwriting');
    }
};
