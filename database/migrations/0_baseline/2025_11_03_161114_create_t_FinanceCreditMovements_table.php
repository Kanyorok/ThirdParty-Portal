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
        Schema::create('t_FinanceCreditMovements', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('CreditID');
            $table->bigInteger('CustomerID');
            $table->string('MovementType', 40);
            $table->decimal('Amount', 18)->comment('Signed amount: Positive = increases available credit, Negative = decreases available credit');
            $table->string('ReferenceType', 60)->nullable();
            $table->bigInteger('ReferenceID')->nullable();
            $table->text('Notes')->nullable();
            $table->dateTime('EffectiveOn')->useCurrent();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn')->useCurrent();
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn')->useCurrent();
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec07fec37dca');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCreditMovements');
    }
};
