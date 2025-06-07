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
        Schema::create('t_BudgetRates', function (Blueprint $table) {
            $table->id('Id');
            $table->string('RateTypeCode')->unique();     // e.g., RATE-001
            $table->string('RateTypeName');               // e.g., Interest Rate
            $table->text('Description')->nullable();      // Optional notes or usage info
            $table->boolean('IsDefault')->default(false); // Flag for default

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
        Schema::dropIfExists('t_BudgetRates');
    }
};
