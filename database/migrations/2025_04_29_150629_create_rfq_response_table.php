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
        Schema::create('t_RFQResponse', function (Blueprint $table) {
            $table->id('Id');
            $table->string('RFQNumber')->unique()->comment('Unique identifier for the RFQ');
            $table->string('SupplierName');
            $table->Integer('Quantity');
            $table->string('Description')->nullable()->comment('Quote Response description');
            $table->decimal('QuotedPrice', 10, 2);
            $table->decimal('TotalPayable', 10, 2);
            $table->string('Currency')->default('USD');
            $table->Integer('DurationDays')->default(0);
            $table->json('RequisitionItems')->nullable();
            $table->foreignId('RFQId')->references('Id')->on('t_RFQ')->onDelete('cascade');
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
        Schema::dropIfExists('t_RFQResponse');
    }
};
