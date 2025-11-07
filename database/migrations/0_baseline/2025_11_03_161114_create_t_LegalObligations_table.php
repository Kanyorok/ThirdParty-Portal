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
        Schema::create('t_LegalObligations', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Title');
            $table->string('SourceType');
            $table->date('DueDate');
            $table->enum('Status', ['Pending', 'Completed', 'Overdue'])->default('Pending');
            $table->text('Description');
            $table->boolean('IsActive')->default(false);
            $table->bigInteger('AssignedTo')->nullable();
            $table->bigInteger('ScheduledID')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legalo__3214ec070e248722');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalObligations');
    }
};
