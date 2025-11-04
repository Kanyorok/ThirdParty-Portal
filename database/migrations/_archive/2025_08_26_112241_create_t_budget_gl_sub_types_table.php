<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('t_BudgetGLSubTypes', function (Blueprint $table) {
            $table->id('Id'); // Primary key

            $table->unsignedBigInteger('AccountID')->nullable();
            $table->string('Description', 255);
            $table->string('CurrencyID', 10)->nullable();
            $table->string('GLAccountTypeID', 5)->nullable();
            $table->unsignedBigInteger('GLSubAccountTypeID')->nullable();
            $table->unsignedBigInteger('GLTypeGroupID')->nullable(); // ✅ Added column

            $table->unsignedBigInteger('CreatedBy')->nullable();
            $table->timestamp('CreatedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
            $table->unsignedBigInteger('DeletedBy')->nullable();
            $table->timestamp('DeletedOn')->nullable();

            // Optional indexes
            $table->index('AccountID');
            $table->index('GLAccountTypeID');
            $table->index('GLSubAccountTypeID');
            $table->index('GLTypeGroupID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_BudgetGLSubTypes');
    }
};
