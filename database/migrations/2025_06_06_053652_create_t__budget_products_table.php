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
        Schema::create('t_BudgetProducts', function (Blueprint $table) {
            $table->id('Id');
            $table->unsignedBigInteger('CBSProductID');
            //$table->foreign('CBSProductID')->references('Id')->on('t_BudgetProductTypes');
            $table->string('Description', 255)->nullable();
            $table->string('ProductTypeID', 10)->nullable();
            $table->foreignId('CurrencyID')->references('Id')->on('t_Currencies');
            $table->unsignedBigInteger('GLAccountID');
            $table->foreign('GLAccountID')->references('Id')->on('t_BudgetGLAccounts');

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
        Schema::dropIfExists('t_BudgetProducts');
    }
};
