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
        Schema::create('t_LegalCaseEvidence', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('LegalCaseID');
            $table->string('EvidenceTitle');
            $table->text('Description');
            $table->string('DMSDocumentID')->nullable();
            $table->string('ExternalLink')->nullable();
            $table->string('IsActive')->default('Active');
            $table->bigInteger('UploadedBy');
            $table->dateTime('UploadedOn');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legalc__3214ec0776011eb5');
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
