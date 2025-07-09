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
        Schema::create('t_BudgetGLMaster', function (Blueprint $table) {
            $table->id('BudgetGLID'); // Primary Key
            $table->string('AccountID', 50); // VARCHAR(50)
            $table->string('Description', 255)->nullable(); // NVARCHAR(255)
            $table->string('CurrencyID', 10); // VARCHAR(10)
            $table->string('GLAccountTypeID', 10); // VARCHAR(10)
            $table->string('GLSubAccountTypeID', 50); // VARCHAR(50)
                        
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
        Schema::dropIfExists('t_BudgetGLMaster');
    }
};
