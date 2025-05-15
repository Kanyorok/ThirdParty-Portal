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
            $table->unsignedBigInteger('TenderID');
            $table->unsignedBigInteger('VendorID');

            // Other columns
            $table->text('Question');
            $table->dateTime('QuestionDate');
            $table->text('Answer')->nullable(); // Nullable since an answer might not be provided immediately
            $table->dateTime('AnswerDate')->nullable(); // Nullable for the same reason
            $table->boolean('ISPUBLISHEDTOALL')->default(false); // Boolean with a default value

            // Define foreign key constraints
           // $table->foreign('TenderID')->references('id')->on('tenders')->onDelete('cascade');
            //$table->foreign('VendorID')->references('id')->on('vendors')->onDelete('cascade');

            // Timestamps (optional, since your table doesn't explicitly list created_at/updated_at)
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