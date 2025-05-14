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
        Schema::create('t_TenderInvitations', function (Blueprint $table) {
            $table->id('InvitationID'); // Primary Key
            $table->unsignedBigInteger('TenderID'); // Foreign Key to Tender
            $table->unsignedBigInteger('SupplierID'); // Foreign Key to Supplier
            $table->dateTime('InvitationDate');
            $table->enum('ResponseStatus', ['Pending', 'Accepted', 'Declined'])->default('Pending');
            $table->dateTime('ResponseDate')->nullable();
            $table->text('DeclineReason')->nullable();
            $table->string('ConfirmationAttachment')->nullable(); // File path or name
            $table->timestamps();

            // Foreign key constraints (optional but recommended)
           // $table->foreign('TenderID')->references('id')->on('tenders')->onDelete('cascade');
           // $table->foreign('SupplierID')->references('id')->on('suppliers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_TenderInvitations');
    }
};
