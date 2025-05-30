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
        Schema::create('t_Tenders', function (Blueprint $table) {
            $table->id('Id');
            $table->string('TenderNo', 50)->unique()->comment('Auto-generated tender number');
            $table->string('Title', 255);
            $table->string('TenderType', 20)->comment('Enum: Open, Restricted');
            $table->string('TenderCategory', 20);
            $table->text('ScopeOfWork')->nullable();
            $table->text('Instructions')->nullable();
            $table->date('SubmissionDeadline');
            $table->date('OpeningDate');
            $table->string('Status', 20)->default('draft')->comment('Enum: Draft, Published, Closed');
            $table->foreignId('ProcurementModeId')->nullable()->comment('Linked procurement mode')->constrained('t_ProcurementModes', 'Id');
            $table->decimal('EstimatedValue', 18, 2)->nullable();
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
        Schema::dropIfExists('t_Tenders');
    }
};
