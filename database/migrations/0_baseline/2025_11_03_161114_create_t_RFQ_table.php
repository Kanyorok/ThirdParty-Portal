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
        Schema::create('t_RFQ', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RFQNumber')->unique();
            $table->text('Comments');
            $table->string('Status')->default('Pending');
            $table->date('SubmissionDeadline');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->text('Remarks')->nullable();
            $table->bigInteger('RequisitionId');

            $table->primary(['Id'], 'pk__t_rfq__3214ec078da3ac6d');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_RFQ');
    }
};
