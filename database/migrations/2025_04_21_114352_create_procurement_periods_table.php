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
        Schema::create('t_ProcurementPeriods', function (Blueprint $table) {
            $table->id('Id');
            $table->string('ProcurementPeriodNumber')->unique()->comment('Unique identifier for the procurement period');
            $table->string('Title')->nullable();
            $table->date('StartDate');
            $table->date('EndDate');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id')->comment('User who created the tender');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id')->comment('User who last modified the tender');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ProcurementPeriods');
    }
};
