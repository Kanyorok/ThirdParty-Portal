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
            $table->bigIncrements('InvitationID');
            $table->bigInteger('TenderId');
            $table->bigInteger('SupplierId');
            $table->dateTime('InvitationDate');
            $table->enum('ResponseStatus', ['Pending', 'Accepted', 'Declined'])->default('Pending');
            $table->dateTime('ResponseDate')->nullable();
            $table->text('DeclineReason')->nullable();
            $table->string('ConfirmationAttachment')->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['InvitationID'], 'pk__t_tender__033c8d2f3276e7d6');
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
