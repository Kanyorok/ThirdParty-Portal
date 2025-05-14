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
            $table->id('Id');
            $table->string('TenderNo', 50)->unique()->comment('Auto-generated tender number');
            $table->string('Title', 255);
            $table->string('TenderType', 20)->comment('Enum: Open, Restricted');
            $table->string('TenderCategory', 20)->comment('Enum: Goods, Services, Works');
            $table->text('ScopeOfWork')->nullable();
            $table->text('Instructions')->nullable();
            $table->date('SubmissionDeadline');
            $table->date('OpeningDate');
            $table->string('Status', 20)->default('draft')->comment('Enum: Draft, Published, Closed');
            $table->unsignedBigInteger('RelatedPRID')->nullable()->comment('Linked requisition ID');
            $table->unsignedBigInteger('ProcurementModeId')->nullable()->comment('Linked procurement mode');
            $table->decimal('EstimatedValue', 18, 2)->nullable();
            $table->string('Currency', 3)->default('USD');
            $table->datetime('DateCreated')->useCurrent();
            $table->unsignedBigInteger('CreatedBy');
            $table->datetime('ModifiedOn')->nullable();
            $table->unsignedBigInteger('ModifiedBy')->nullable();
            $table->datetime('PublishedAt')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('RelatedPRID')->references('id')->on('t_Requisitions')->onDelete('set null');
            $table->foreign('ProcurementModeId')->references('id')->on('t_ProcurementModes')->onDelete('set null');
            $table->foreign('CreatedBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ModifiedBy')->references('id')->on('users')->onDelete('set null');
        });

        // Add index for better performance
        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->index('Status');
            $table->index('TenderType');
            $table->index('TenderCategory');
            $table->index('SubmissionDeadline');
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
