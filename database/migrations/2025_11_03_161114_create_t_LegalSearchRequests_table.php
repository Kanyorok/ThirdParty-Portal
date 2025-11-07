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
        Schema::create('t_LegalSearchRequests', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('RequestType');
            $table->string('EntityName');
            $table->string('Status')->default('Pending');
            $table->text('Remarks');
            $table->text('Findings')->nullable();
            $table->text('ApprovalReason')->nullable();
            $table->bigInteger('RequestedBy');
            $table->dateTime('RequestDate');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['Id'], 'pk__t_legals__3214ec07b9e2c9dd');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_LegalSearchRequests');
    }
};
