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
        Schema::create('t_BudgetDriversMaster', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('DriverName', 50)->unique();
            $table->bigInteger('DriverTypeID');
            $table->boolean('IsActive')->default(true);
            $table->string('Frequency')->default('Annualy');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_budget__3214ec076ccf3c2e');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BudgetDriversMaster');
    }
};
