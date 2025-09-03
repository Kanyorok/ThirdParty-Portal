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
    Schema::create('t_ComplianceObligations', function (Blueprint $table) {
        $table->id('Id');
        $table->string('Title', 255);
        $table->text('Description')->nullable();
        $table->foreignId('RegulatorID')->constrained('t_RegulatoryBodies', 'Id');
        $table->foreignId('ComplianceAreaID')->constrained('t_ComplianceAreas', 'Id');
        $table->date('EffectiveDate')->nullable();
        $table->boolean('IsActive')->default(1);

        $table->unsignedBigInteger('CreatedBy');
        $table->timestamp('CreatedOn')->useCurrent();
        $table->unsignedBigInteger('ModifiedBy')->nullable();
        $table->timestamp('ModifiedOn')->nullable();
        $table->unsignedBigInteger('DeletedBy')->nullable();
        $table->timestamp('DeletedOn')->nullable();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
     Schema::dropIfExists('t_ComplianceObligations');
    }
};
