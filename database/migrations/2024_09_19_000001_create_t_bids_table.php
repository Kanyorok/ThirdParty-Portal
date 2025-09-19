<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('t_Bids', function (Blueprint $table) {
            $table->id('Id');
            
            // Business fields
            $table->foreignId('TenderId')->constrained('t_Tenders', 'Id');
            $table->foreignId('SupplierId')->constrained('t_Suppliers', 'Id');
            $table->decimal('BidAmount', 15, 2);
            $table->string('Currency', 3);
            $table->integer('ValidityPeriod');
            $table->integer('DeliveryPeriod');
            $table->text('PaymentTerms')->nullable();
            $table->enum('Status', ['draft', 'submitted'])->default('draft');
            
            // Your mandatory audit fields
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('DeletedOn')->nullable();
            
            // Indexes
            $table->index(['TenderId', 'Status']);
            $table->index('SupplierId');
        });
    }

    public function down()
    {
        Schema::dropIfExists('t_Bids');
    }
};
