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
        Schema::create('t_GLClosingBalances', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('GLAccountID');
            $table->bigInteger('BranchID')->nullable();
            $table->dateTime('BalanceDate');
            $table->decimal('OpeningBalance', 18, 5)->default(0);
            $table->decimal('ClosingBalance', 18, 5)->default(0);
            $table->decimal('LocalBalance', 18, 5)->default(0);
            $table->decimal('ForeignBalance', 18, 5)->default(0);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_glclos__3214ec0743976443');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_GLClosingBalances');
    }
};
