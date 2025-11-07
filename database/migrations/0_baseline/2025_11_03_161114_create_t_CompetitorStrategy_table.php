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
        Schema::create('t_CompetitorStrategy', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('CompetitorId');
            $table->bigInteger('StrategyId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');

            $table->primary(['id'], 'pk__t_compet__3213e83fcb459b4f');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CompetitorStrategy');
    }
};
