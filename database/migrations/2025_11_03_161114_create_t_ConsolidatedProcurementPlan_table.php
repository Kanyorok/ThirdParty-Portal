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
        Schema::create('t_ConsolidatedProcurementPlan', function (Blueprint $table) {
            $table->bigIncrements('PlanID');
            $table->string('Title');
            $table->string('ReferenceNumber');
            $table->string('FiscalYear');
            $table->string('Status');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedDate');
            $table->bigInteger('SubmittedBy');
            $table->dateTime('SubmittedDate');
            $table->string('CurrentApprLevel');
            $table->text('Remarks')->nullable();
            $table->bigInteger('ModifiedBy');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('CreatedOn');
            $table->dateTime('ModifiedOn');
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['PlanID'], 'pk__t_consol__755c22d70e12e7b9');
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
