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
        Schema::create('t_LegalDocuments', function (Blueprint $table) {
            $table->id('Id');
            $table->string('DocumentTitle', 255)->nullable();
            $table->string('DocumentType', 100)->nullable(); // e.g. Contract, Lease, NDA
            $table->string('SourceModule', 100)->nullable(); // e.g. Procurement, Property, HR, Legal
            $table->foreignId('SourceID')->constrained('t_Modules', 'ModuleID')->nullable(); // ID from source table (nullable for internal docs)
            $table->bigInteger('LinkedDMSDocID')->nullable(); // FK to DMS document ID
            $table->string('ReviewStatus', 50)->nullable(); // Draft, In Review, Approved, Rejected
            $table->enum('ExecutionStatus', ['Pending', 'Signed', 'Archived'])->nullable(); // Pending, Signed, Archived
            $table->dateTime('DispatchDate')->nullable();
            $table->dateTime('SignOffDate')->nullable();
            $table->foreignId('ReviewedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ReviewedOn');
            $table->text('Remarks')->nullable();
            $table->boolean('IsActive')->default(1);

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
        Schema::dropIfExists('t_LegalDocuments');
    }
};
