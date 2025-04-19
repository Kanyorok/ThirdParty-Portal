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
        Schema::create('t_Suppliers', function (Blueprint $table) {
            $table->id('Id');
            $table->string('SupplierName');
            $table->string('ContactEmail')->nullable();
            $table->string('ContactPhone')->nullable();
            $table->text('Address')->nullable();
            $table->boolean('IsPrequalified')->default(false); // for prequalification
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Suppliers');
    }
};
