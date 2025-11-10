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
        Schema::create('t_AssignRequest', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('RequestNumber')->constrained('t_MaintenanceRequest', 'Id');
            $table->date('AssignmentDate');
            $table->foreignId('AssignmentType')->constrained('t_CodeDetails', 'ID');
            $table->foreignId('InternalTechnician')->nullable()->constrained('t_Employees', 'Id');
            $table->foreignId('PrequalifiedVendor')->nullable()->constrained('t_Suppliers', 'Id');
            $table->date('ExpectedStartDate');
            $table->date('ExpectedCompletion');
            $table->string('Status',1);
            $table->foreignId('PriorityLevel')->constrained('t_CodeDetails', 'ID');
            $table->string('InstructionNotes')->nullable();
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
        Schema::dropIfExists('t_AssignRequest');
    }
};
