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
        Schema::create('t_BudgetDriversMaster', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DriverName', 50)->unique();
            //$table->string('DriverCode')->unique();
            $table->foreignId('DriverTypeID')->constrained('t_BudgetDrivers', 'Id');
            //$table->foreignId('UOMID')->constrained('t_UOM','Id');
            $table->boolean('IsActive')->default(true);
            $table->string('Frequency')->default('Annualy'); //annualy, semi-anual, quartely, monthly,

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
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
