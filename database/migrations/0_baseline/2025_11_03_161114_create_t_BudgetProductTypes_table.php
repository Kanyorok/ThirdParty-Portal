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
        Schema::create('t_BudgetProductTypes', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('ProductCode', 10)->unique();
            $table->string('Name', 100);
            $table->string('Description')->nullable();
            $table->string('CBSCode', 10);
            $table->dateTime('LastSyncDate')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec077ac8c2ff');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetProductTypes');
    }
};
