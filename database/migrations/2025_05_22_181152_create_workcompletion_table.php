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
        Schema::create('t_WorkCompletion', function (Blueprint $table) {
            $table->id('Id');
            $table->foreignId('RequestNumber')->constrained('t_AssignRequest', 'Id');
            $table->date('CompletionDate');
            $table->string('WorkDoneSummary');
            $table->string('PartsUsed');
            $table->integer('Cost');
            $table->foreignId('FinalStatus')->constrained('t_CodeDetails', 'ID');
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
        Schema::dropIfExists('t_WorkCompletion');
    }
};
