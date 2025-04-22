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
        Schema::create('t_SasraAuditors', function (Blueprint $table) {
            $table->id('Id');
            $table->string('FirmName');
            $table->string('PhysicalAddress')->nullable();
            $table->string('PostalAddress')->nullable();
            $table->string('Town')->nullable();
            $table->string('Status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t__SasraAuditors');
    }
};
