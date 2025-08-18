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
            $table->string('SourceType')->constrained('t_CodeDetails','Value');
            $table->date('DueDate');
            $table->enum('Status', ['Pending', 'Completed', 'Overdue'])->default('Pending');
            $table->text('Description');
            $table->boolean('IsActive')->default(false);
            $table->foreignId('AssignedTo')->nullable()->constrained('t_Users', 'Id');
            $table->unsignedBigInteger('ScheduledID')->constrained('t_Schedule', 'ScheduleID')->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('t_LegalObligations');
    }
};
