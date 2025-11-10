<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_BudgetDriverRates', function (Blueprint $table) {
            // Drop old foreign key
            $table->dropForeign(['ProductTypeID']);

            // Add new foreign key referencing t_BudgetProducts
            $table->foreign('ProductTypeId')->references('Id')->on('t_BudgetProducts');
        });
    }

    public function down(): void
    {
        Schema::table('t_BudgetDriverRates', function (Blueprint $table) {
            // Rollback: drop new and restore old foreign key
            $table->dropForeign(['ProductTypeId']);
            $table->foreign('ProductTypeId')->references('Id')->on('t_BudgetProductTypes');
        });
    }
};
