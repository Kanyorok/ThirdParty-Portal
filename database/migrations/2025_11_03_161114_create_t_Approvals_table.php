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
        Schema::create('t_Approvals', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('DocType');
            $table->bigInteger('DocumentId');
            $table->bigInteger('UserId');
            $table->string('Status', 15)->nullable();
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('RejectionReason', 500)->nullable();

            $table->primary(['Id'], 'pk__t_approv__3214ec07ff8a634a');
            $table->unique(['DocType', 'DocumentId', 'UserId']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_Approvals');
    }
};
