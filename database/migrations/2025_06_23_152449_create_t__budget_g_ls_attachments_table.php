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
        Schema::create('t_BudgetGLsAttachments', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('BudgetID')->constrained('t_Budgets', 'Id')->onDelete('cascade');
            $table->string('GLID')->constrained('t_BudgetGLMaster', 'BudgetGLID')->onDelete('cascade');// store the ID from that table which is called BudgetGLID
            $table->string('AccountID');
            $table->string('Description')->nullable();
            $table->string('GLAccountTypeID', 10);// Store like A, E,I,L

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
        Schema::dropIfExists('t_BudgetGLsAttachments');
    }
};
