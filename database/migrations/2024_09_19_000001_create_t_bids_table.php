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
            $table->unsignedBigInteger('TenderId');
            $table->unsignedBigInteger('SupplierId');
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

        // Add FKs only if referenced tables already exist (supports flexible migration ordering)
        if (Schema::hasTable('t_Tenders')) {
            Schema::table('t_Bids', function (Blueprint $table) {
                $table->foreign('TenderId')->references('Id')->on('t_Tenders');
            });
        }
        if (Schema::hasTable('t_Suppliers')) {
            Schema::table('t_Bids', function (Blueprint $table) {
                $table->foreign('SupplierId')->references('Id')->on('t_Suppliers');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('t_Bids');
    }
};
