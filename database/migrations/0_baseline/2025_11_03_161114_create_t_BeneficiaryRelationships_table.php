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
        Schema::create('t_BeneficiaryRelationships', function (Blueprint $table) {
            $table->bigIncrements('ID');
            $table->string('Code', 50)->unique();
            $table->string('Name', 100);
            $table->boolean('IsActive')->default(true);

            $table->primary(['ID'], 'pk__t_benefi__3214ec27f39939ef');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BeneficiaryRelationships');
    }
};
