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
        Schema::create('t_CashbookLines', function (Blueprint $table) {
            $table->bigIncrements('LineID');
            $table->bigInteger('CashbookID');
            $table->bigInteger('GLAccountID')->nullable();
            $table->string('Description', 300)->nullable();
            $table->decimal('AmountDr', 18)->default(0);
            $table->decimal('AmountCr', 18)->default(0);
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();

            $table->primary(['LineID'], 'pk__t_cashbo__2eae64c9c556dc5d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_CashbookLines');
    }
};
