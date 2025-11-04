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
        Schema::create('t_BudgetReallocationLog', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('ReallocationID');
            $table->bigInteger('BudgetID');
            $table->bigInteger('LimitID');
            $table->bigInteger('BudgetLineID');
            $table->bigInteger('ActivityID')->nullable();
            $table->decimal('BeforeAmount', 15);
            $table->decimal('AfterAmount', 15);
            $table->dateTime('CreatedOn')->useCurrent();

            $table->primary(['id'], 'pk__t_budget__3213e83f4d696a0e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetReallocationLog');
    }
};
