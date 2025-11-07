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
        Schema::create('t_MarketingPlannerActivityUsers', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('UserID');
            $table->bigInteger('ActivityId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_market__3214ec072f3aa8b4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_MarketingPlannerActivityUsers');
    }
};
