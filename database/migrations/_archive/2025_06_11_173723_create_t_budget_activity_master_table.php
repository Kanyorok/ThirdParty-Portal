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
        Schema::create('t_BudgetActivityMaster', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BudgetLineID')->constrained('t_BudgetLines', 'Id');
            //$table->string('ActivityCode', 20)->unique();
            $table->string('ActivityName')->unique();
            $table->text('Description');
            $table->boolean('IsActive')->default(true);

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
        Schema::dropIfExists('t_BudgetActivityMaster');
    }
};
