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
            $table->bigIncrements('Id');
            $table->string('RFQNumber');
            $table->string('SupplierName');
            $table->decimal('TotalPayable', 10);
            $table->string('Currency')->default('KES');
            $table->integer('DurationDays')->default(0);
            $table->bigInteger('RFQId');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('RFQResponseNumber')->unique();
            $table->bigInteger('SupplierId')->nullable();
            $table->string('Status', 16)->default('DRAFT');
            $table->dateTime('SubmittedOn')->nullable();

            $table->primary(['Id'], 'pk__t_rfqres__3214ec07f19be4d6');
            $table->unique(['RFQId', 'SupplierId'], 'uq_t_rfqresponse_rfq_supplier');
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
