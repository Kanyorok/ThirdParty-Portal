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
            $table->id('Id');
            $table->foreignId('RfqResponseId')->constrained('t_RFQResponse')->onDelete('cascade');
            $table->string('ItemName');
            $table->string('UOM')->nullable();
            $table->integer('Quantity');
            $table->decimal('QuotedPrice', 15, 2);
            $table->decimal('TotalPayable', 15, 2);
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
        Schema::dropIfExists('t_ResponseItems');
    }
};
