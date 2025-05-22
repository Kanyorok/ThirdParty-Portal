<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::create('t_ConsolidatedProcurementPlan', function (Blueprint $table) {
            $table->id('PlanID'); // Custom primary key
            $table->string('Title');
            $table->string('ReferenceNumber');
            $table->string('FiscalYear');
            $table->string('Status');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedDate');
            $table->foreignId('SubmittedBy')->constrained('t_Users', 'Id');
            $table->dateTime('SubmittedDate');
            $table->string('CurrentApprLevel');
            $table->text('Remarks')->nullable();
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->dateTime('ModifiedOn');
            $table->softDeletes('DeletedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_ConsolidatedProcurementPlan');
    }
};