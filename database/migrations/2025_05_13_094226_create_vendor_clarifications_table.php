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
            // Primary key
            $table->unsignedBigInteger('ClarificationID')->autoIncrement();

            // Foreign keys
            $table->foreignId('TenderID')->constrained('t_Tenders', 'Id');
            $table->foreignId('VendorID')->constrained('t_Suppliers', 'Id');

            // Other columns
            $table->text('Question');
            $table->dateTime('QuestionDate');
            $table->text('Answer')->nullable(); // Nullable since an answer might not be provided immediately
            $table->dateTime('AnswerDate')->nullable(); // Nullable for the same reason
            $table->boolean('ISPUBLISHEDTOALL')->default(false); // Boolean with a default value
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');


            $table->timestamps();
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
