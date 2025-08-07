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
        Schema::create('t_FinanceJournalEntries', function (Blueprint $table) {
            $table->id('Id');
            $table->date('Date');
            $table->string('RefNo')->unique();
            $table->enum('Type', ['normal', 'recurring', 'reversing'])->default('normal');
            $table->enum('ApprovalStatus', ['draft', 'posted','rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->text('Description')->nullable();
            $table->string('SystemDescription', 255)->nullable();

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
        Schema::dropIfExists('t_FinanceJournalEntries');
    }
};
