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
            $table->foreignId('TenderId')->constrained('t_Tenders', 'Id'); // Foreign Key to Tender
            $table->foreignId('SupplierId')->constrained('t_Suppliers', 'Id'); // Foreign Key to Supplier
            $table->dateTime('InvitationDate');
            $table->enum('ResponseStatus', ['Pending', 'Accepted', 'Declined'])->default('Pending');
            $table->dateTime('ResponseDate')->nullable();
            $table->text('DeclineReason')->nullable();
            $table->string('ConfirmationAttachment')->nullable(); // File path or name
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
        Schema::dropIfExists('t_TenderInvitations');
    }
};
