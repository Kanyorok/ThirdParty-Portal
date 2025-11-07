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
        Schema::create('t_Tenders', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('TenderNo', 50)->unique();
            $table->string('Title');
            $table->string('TenderType', 20);
            $table->string('TenderCategory', 20);
            $table->text('ScopeOfWork')->nullable();
            $table->text('Instructions')->nullable();
            $table->date('SubmissionDeadline');
            $table->date('OpeningDate');
            $table->string('Status', 20)->default('draft');
            $table->bigInteger('ProcurementModeId')->nullable();
            $table->decimal('EstimatedValue', 15)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->bigInteger('RelatedPRID')->nullable();
            $table->bigInteger('CurrencyId');
            $table->bigInteger('ItemCategoryId')->nullable();
            $table->text('ApprovalRemarks')->nullable();
            $table->tinyInteger('ApprovalStatus')->default(0);

            $table->primary(['Id'], 'pk__t_tender__3214ec073d702ac8');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Tenders');
    }
};
