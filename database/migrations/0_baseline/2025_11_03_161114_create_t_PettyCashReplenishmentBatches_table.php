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
        Schema::create('t_PettyCashReplenishmentBatches', function (Blueprint $table) {
            $table->bigIncrements('BatchID');
            $table->bigInteger('FloatID');
            $table->bigInteger('BankAccountID');
            $table->date('BatchDate');
            $table->decimal('TotalAmount', 18)->default(0);
            $table->string('Status', 20)->default('Draft');
            $table->bigInteger('CashbookID')->nullable();
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('CreatedBy')->nullable();
            $table->dateTime('ModifiedOn')->nullable();
            $table->bigInteger('ModifiedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('DeletedBy')->nullable();

            $table->primary(['BatchID'], 'pk__t_pettyc__5d55ce38992209d3');
            $table->index(['FloatID', 'Status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_PettyCashReplenishmentBatches');
    }
};
