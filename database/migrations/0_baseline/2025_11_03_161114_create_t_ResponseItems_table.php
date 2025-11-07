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
        Schema::create('t_ResponseItems', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RfqResponseId');
            $table->string('ItemName');
            $table->string('UOM')->nullable();
            $table->integer('Quantity');
            $table->decimal('QuotedPrice', 15);
            $table->decimal('TotalPayable', 15);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_respon__3214ec07c0a43b45');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ResponseItems');
    }
};
