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
        Schema::create('t_AssignRequest', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RequestNumber');
            $table->date('AssignmentDate');
            $table->bigInteger('AssignmentType');
            $table->bigInteger('InternalTechnician')->nullable();
            $table->bigInteger('PrequalifiedVendor')->nullable();
            $table->date('ExpectedStartDate');
            $table->date('ExpectedCompletion');
            $table->string('Status', 1);
            $table->bigInteger('PriorityLevel');
            $table->string('InstructionNotes')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_assign__3214ec07ac298f15');
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
