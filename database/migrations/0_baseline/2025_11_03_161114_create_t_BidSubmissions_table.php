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
        Schema::create('t_BidSubmissions', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('TenderRef');
            $table->string('SupplierName');
            $table->bigInteger('SubmissionMode');
            $table->dateTime('ReceivedAt');
            $table->text('Remarks')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('SupplierId')->nullable();
            $table->text('EncryptedDocuments')->nullable();
            $table->text('EncryptionKey')->nullable();
            $table->enum('SubmissionSource', ['manual', 'portal'])->default('manual');
            $table->boolean('DocumentsAccessible')->default(false);
            $table->dateTime('BidOpeningDate')->nullable();
            $table->string('RecordedBy')->nullable();
            $table->binary('EncryptionEnvelope')->nullable();
            $table->decimal('BidAmount', 15)->nullable();
            $table->string('Currency', 3)->nullable();
            $table->integer('ValidityPeriod')->nullable();
            $table->integer('DeliveryPeriod')->nullable();
            $table->text('PaymentTerms')->nullable();
            $table->string('BidStatus', 50)->default('submitted');
            $table->dateTime('OpenedAt')->nullable();
            $table->bigInteger('OpenedBy')->nullable();
            $table->boolean('ReceivedOnTime')->nullable();
            $table->decimal('TechnicalScore', 5)->nullable();
            $table->decimal('FinancialScore', 5)->nullable();
            $table->decimal('TotalScore', 5)->nullable();
            $table->boolean('IsResponsive')->nullable();
            $table->text('ResponsivenessRemarks')->nullable();
            $table->text('EvaluationNotes')->nullable();
            $table->string('CeremonyType', 20)->nullable();
            $table->text('CeremonyNotes')->nullable();
            $table->text('OfficersPresent')->nullable();
            $table->text('ReadOutSummary')->nullable();
            $table->boolean('BidSecurityPresent')->nullable();
            $table->boolean('SubmittedTimely')->nullable();
            $table->boolean('HasMandatoryDocuments')->nullable();
            $table->boolean('IsEligible')->nullable();
            $table->text('TimelySubmissionRemarks')->nullable();
            $table->text('DocumentComplianceRemarks')->nullable();
            $table->text('EligibilityRemarks')->nullable();
            $table->dateTime('ResponsivenessCheckedAt')->nullable();
            $table->bigInteger('ResponsivenessCheckedBy')->nullable();
            $table->bigInteger('TenderSupplierID')->nullable();

            $table->primary(['Id'], 'pk__t_bidsub__3214ec076be9a6c4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BidSubmissions');
    }
};
