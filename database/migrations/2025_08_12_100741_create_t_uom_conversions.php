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
        Schema::create('t_UOMConversions', function (Blueprint $table) {

            $table->id('Id');
            $table->string('UOMNo')->unique();
            $table->foreignId('Item')->constrained('t_Items', 'Id');
            $table->foreignId('UOM')->constrained('t_UOM', 'Id');
            $table->foreignId('AlternateUOM')->constrained('t_UOM', 'Id');
            $table->string('ConversionFactor')->nullable();
            $table->string('Remarks')->nullable();       
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->nullable()->constrained('t_Users', 'Id');
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
        Schema::dropIfExists('t_UOMConversions');
    }
};
