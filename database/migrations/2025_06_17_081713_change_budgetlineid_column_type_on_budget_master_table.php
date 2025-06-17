<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        
        DB::statement('ALTER TABLE t_BudgetLineLink DROP CONSTRAINT t_budgetlinelink_budgetlineid_foreign');

       
        DB::statement('ALTER TABLE t_BudgetMaster DROP CONSTRAINT t_budgetmaster_budgetlineid_primary');

       
        Schema::table('t_BudgetMaster', function (Blueprint $table) {
            $table->integer('BudgetLineID')->nullable(false)->change();
        });

        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->integer('BudgetLineID')->change(); 
        });

        
        DB::statement('ALTER TABLE t_BudgetMaster ADD CONSTRAINT t_budgetmaster_budgetlineid_primary PRIMARY KEY (BudgetLineID)');

        
        DB::statement('ALTER TABLE t_BudgetLineLink ADD CONSTRAINT t_budgetlinelink_budgetlineid_foreign 
                       FOREIGN KEY (BudgetLineID) REFERENCES t_BudgetMaster(BudgetLineID)');
    }

    public function down(): void
    {
       
        DB::statement('ALTER TABLE t_BudgetLineLink DROP CONSTRAINT t_budgetlinelink_budgetlineid_foreign');
        DB::statement('ALTER TABLE t_BudgetMaster DROP CONSTRAINT t_budgetmaster_budgetlineid_primary');

        
        Schema::table('t_BudgetMaster', function (Blueprint $table) {
            $table->string('BudgetLineID')->nullable(false)->change();
        });

        Schema::table('t_BudgetLineLink', function (Blueprint $table) {
            $table->string('BudgetLineID')->change();
        });

        
        DB::statement('ALTER TABLE t_BudgetMaster ADD CONSTRAINT t_budgetmaster_budgetlineid_primary PRIMARY KEY (BudgetLineID)');

        DB::statement('ALTER TABLE t_BudgetLineLink ADD CONSTRAINT t_budgetlinelink_budgetlineid_foreign 
                       FOREIGN KEY (BudgetLineID) REFERENCES t_BudgetMaster(BudgetLineID)');
    }
};