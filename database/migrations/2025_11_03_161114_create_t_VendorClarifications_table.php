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
        Schema::create('t_VendorClarifications', function (Blueprint $table) {
            $table->bigIncrements('ClarificationID');
            $table->bigInteger('TenderID');
            $table->bigInteger('VendorID');
            $table->text('Question');
            $table->dateTime('QuestionDate');
            $table->text('Answer')->nullable();
            $table->dateTime('AnswerDate')->nullable();
            $table->boolean('ISPUBLISHEDTOALL')->default(false);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['ClarificationID'], 'pk__t_vendor__62c00bc4e0c4f374');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_VendorClarifications');
    }
};
