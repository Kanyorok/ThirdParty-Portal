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
            $table->bigIncrements('Id');
            $table->date('Date');
            $table->string('RefNo')->unique();
            $table->string('IdempotencyKey', 150)->nullable();
            $table->enum('Type', ['normal', 'recurring', 'reversing'])->default('normal');
            $table->enum('ApprovalStatus', ['draft', 'posted', 'rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->text('Description')->nullable();
            $table->string('CurrencyID', 10)->nullable();
            $table->string('SystemDescription')->nullable();
            $table->string('Status', 20)->default('draft');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->boolean('IsReversed')->default(false);
            $table->string('SourceModule')->default('1100000');

            $table->primary(['Id'], 'pk__t_financ__3214ec07fd21dc4a');
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
