<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_ComplianceFilingTemplates', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Name', 200);
            $table->foreignId('FilingTypeID')->constrained('t_FilingTypes', 'Id');
            $table->foreignId('RegulatorID')->constrained('t_RegulatoryBodies', 'Id');
            $table->foreignId('FormatID')->nullable()->constrained('t_FileFormats', 'Id');
            $table->string('Frequency', 50)->nullable(); // Monthly/Quarterly/Annually
            $table->string('DueDay', 50)->nullable();   // e.g., "15th of every month"
            $table->string('PortalURL', 500)->nullable()->after('DueDay');
            $table->text('Description')->nullable();
            $table->boolean('IsActive')->default(1);

            $table->unsignedBigInteger('CreatedBy');
            $table->timestamp('CreatedOn')->useCurrent();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->timestamp('ModifiedOn')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ComplianceFilingTemplates');
    }
};
