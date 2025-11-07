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
        Schema::create('t_Bids', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('TenderId');
            $table->bigInteger('SupplierId')->index();
            $table->decimal('BidAmount', 15);
            $table->string('Currency', 3);
            $table->integer('ValidityPeriod');
            $table->integer('DeliveryPeriod');
            $table->text('PaymentTerms')->nullable();
            $table->enum('Status', ['draft', 'submitted'])->default('draft');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_bids__3214ec07367a9f47');
            $table->index(['TenderId', 'Status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Bids');
    }
};
