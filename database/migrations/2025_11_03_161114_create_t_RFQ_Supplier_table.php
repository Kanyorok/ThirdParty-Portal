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
        Schema::create('t_RFQ_Supplier', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RFQId');
            $table->bigInteger('SupplierId');
            $table->string('Status')->default('Pending');
            $table->timestamps();
            $table->dateTime('CreatedOn')->nullable();
            $table->dateTime('ModifiedOn')->nullable();

            $table->primary(['Id'], 'pk__t_rfq_su__3214ec0783311ce3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQ_Supplier');
    }
};
