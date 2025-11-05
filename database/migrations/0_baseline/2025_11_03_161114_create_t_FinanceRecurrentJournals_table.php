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
        Schema::create('t_FinanceRecurrentJournals', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('JournalEntryId');
            $table->date('StartDate');
            $table->date('CuttOffDate')->nullable();
            $table->date('NextRunDate')->nullable();
            $table->enum('Frequency', ['d', 'w', 'm', 'q', 'y']);
            $table->string('ReferenceName');
            $table->text('Description')->nullable();
            $table->boolean('isVoucher')->default(false);
            $table->string('SystemDescription')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec0723574a38');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceRecurrentJournals');
    }
};
