<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_BudgetLineProductTypes', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('ProductTypeId');
        });

        //todo fix @martin nullable
        Schema::table('t_BudgetLineProductTypes', static function (Blueprint $table) {
            $table->foreignId('ProductTypeId')->nullable()->references('Id')->on('t_BudgetProducts');
        });

        Schema::table('t_BudgetDriverRates', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('ProductTypeID');
        });

        //todo fix @martin nullable
        Schema::table('t_BudgetDriverRates', static function (Blueprint $table) {
            $table->foreignId('ProductTypeId')->nullable()->references('Id')->on('t_BudgetProducts');
        });
    }

    public function down(): void
    {
        //todo fix @martin
        Schema::table('t_BudgetLineProductTypes', function (Blueprint $table) {
            // Rollback: drop new and restore old foreign key
            $table->dropForeign(['ProductTypeId']);
            $table->foreign('ProductTypeId')->references('Id')->on('t_BudgetProductTypes');
        });

        Schema::table('t_BudgetDriverRates', function (Blueprint $table) {
            // Rollback: drop new and restore old foreign key
            $table->dropForeign(['ProductTypeId']);
            $table->foreign('ProductTypeId')->references('Id')->on('t_BudgetProductTypes');
        });
    }
};
