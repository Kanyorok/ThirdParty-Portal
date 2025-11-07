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
        Schema::create('t_FinanceCDNotes', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('CDNumber')->unique();
            $table->string('NoteType');
            $table->bigInteger('InvoiceRefNo');
            $table->date('NoteDate');
            $table->decimal('NoteAmount');
            $table->enum('ApprovalStatus', ['draft', 'posted', 'rejected'])->default('draft');
            $table->text('ApprovalReason')->nullable();
            $table->string('Status')->nullable();
            $table->text('Description');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_financ__3214ec078a151caf');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceCDNotes');
    }
};
