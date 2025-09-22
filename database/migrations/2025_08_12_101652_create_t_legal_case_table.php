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
        Schema::create('t_LegalCases', function (Blueprint $table) {
            $table->id('Id'); // Primary key
            $table->string('CaseTitle');
            $table->string('CaseNumber');
            $table->string('CourtName');
            $table->date('FilingDate');
            $table->string('OpposingParty');
            $table->string('CaseType');
            $table->string('Status')->default('Open');
            $table->text('Summary');
            $table->unsignedBigInteger('AssignedCounselID')->nullable();
            $table->unsignedBigInteger('CaseDMSDocID')->nullable();
            $table->boolean('IsActive')->default(true);

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
        Schema::dropIfExists('t_LegalCases');
    }
};
