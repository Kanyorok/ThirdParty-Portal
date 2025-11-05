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
        Schema::table('t_RFQEvaluations', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RFQId'])->references(['Id'])->on('t_RFQ')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQEvaluations', function (Blueprint $table) {
            $table->dropForeign('t_rfqevaluations_createdby_foreign');
            $table->dropForeign('t_rfqevaluations_deletedby_foreign');
            $table->dropForeign('t_rfqevaluations_modifiedby_foreign');
            $table->dropForeign('t_rfqevaluations_rfqid_foreign');
        });
    }
};
