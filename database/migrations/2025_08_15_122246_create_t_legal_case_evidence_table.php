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
        Schema::create('t_LegalCaseEvidence', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('LegalCaseID')->constrained('t_LegalCases', 'Id');
            $table->string('EvidenceTitle', 255);
            $table->text('Description');
            $table->string('DMSDocumentID')->nullable(); // Reference to DMS if available
            $table->string('ExternalLink')->nullable();  // Optional external file URL
            $table->string('IsActive')->default('Active');

            $table->unsignedBigInteger('UploadedBy')->constrained('t_Users', 'Id');
            $table->dateTime('UploadedOn');

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
        Schema::dropIfExists('t_LegalCaseEvidence');
    }
};
