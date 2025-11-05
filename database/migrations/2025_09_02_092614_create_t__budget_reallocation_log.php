<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_BudgetReallocationLog', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ReallocationID')->constrained('t_BudgetReallocations');
            $table->unsignedBigInteger('BudgetID');
            $table->foreignId('LimitID')->constrained('t_BudgetLineLedgerLimits');
            $table->foreignId('BudgetLineID')->constrained('t_BudgetLines');
            $table->foreignId('ActivityID')->nullable()->constrained('t_BudgetActivityMaster');
            $table->decimal('BeforeAmount', 15, 2);
            $table->decimal('AfterAmount', 15, 2);
            $table->timestamp('CreatedOn')->useCurrent();
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
