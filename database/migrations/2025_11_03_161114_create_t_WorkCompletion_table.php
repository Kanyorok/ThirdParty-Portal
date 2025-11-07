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
        Schema::create('t_WorkCompletion', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->bigInteger('RequestNumber');
            $table->date('CompletionDate');
            $table->string('WorkDoneSummary');
            $table->string('PartsUsed');
            $table->integer('Cost');
            $table->bigInteger('FinalStatus');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_workco__3214ec076bc1c074');
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
