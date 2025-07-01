<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->unsignedBigInteger('BudgetMasterId')->nullable();

            $table->foreign('BudgetMasterId')
                ->references('Id')
                ->on('t_BudgetMaster')
                ->onDelete('cascade'); // or restrict/null as needed
        });
    }

    public function down(): void
    {
        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->dropForeign(['BudgetMasterId']);
            $table->dropColumn('BudgetMasterId');
        });
    }
};
