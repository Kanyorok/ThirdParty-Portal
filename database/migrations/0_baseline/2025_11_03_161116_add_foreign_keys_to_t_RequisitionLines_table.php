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
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Item'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequisitionID'])->references(['Id'])->on('t_Requisitions')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StatusID'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UrgencyID'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
            $table->dropForeign('t_requisitionlines_createdby_foreign');
            $table->dropForeign('t_requisitionlines_deletedby_foreign');
            $table->dropForeign('t_requisitionlines_item_foreign');
            $table->dropForeign('t_requisitionlines_modifiedby_foreign');
            $table->dropForeign('t_requisitionlines_requisitionid_foreign');
            $table->dropForeign('t_requisitionlines_statusid_foreign');
            $table->dropForeign('t_requisitionlines_urgencyid_foreign');
        });
    }
};
