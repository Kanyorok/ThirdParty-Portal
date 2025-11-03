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
            $table->id('Id');
            $table->foreignId('RFQId')->constrained('t_RFQ', 'Id')->onDelete('cascade');
            $table->foreignId('SupplierId')->constrained('t_Suppliers', 'Id')->onDelete('cascade');
            $table->string('Status')->default('Pending')->comment('Status of the supplier in the RFQ process');
            $table->timestamps();
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