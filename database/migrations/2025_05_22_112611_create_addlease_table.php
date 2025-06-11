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
        Schema::create('t_AddLease', function (Blueprint $table) {
            $table->id();
            $table->string('Tenant');
            $table->string('PropertyID');
            $table->string('BlockID');
            $table->string('FloorID');
            $table->string('Unit');
            $table->date('StartDate');
            $table->date('EndDate');
            $table->string('PaymentFrequency');
            $table->integer('MonthlyRent');
            $table->integer('Deposit');
            $table->integer('DueDay');
            $table->string('SpecialTerms');
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
        Schema::dropIfExists('t_AddLease');
    }
};
