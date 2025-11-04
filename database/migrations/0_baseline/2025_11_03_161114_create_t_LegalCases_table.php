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
        Schema::create('t_LegalCases', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('CaseTitle');
            $table->string('CaseNumber');
            $table->string('CourtName');
            $table->date('FilingDate');
            $table->string('OpposingParty');
            $table->string('CaseType');
            $table->string('Status')->default('Open');
            $table->text('Summary');
            $table->bigInteger('AssignedCounselID')->nullable();
            $table->bigInteger('CaseDMSDocID')->nullable();
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legalc__3214ec075f23cbcb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalCases');
    }
};
