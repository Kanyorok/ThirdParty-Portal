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
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FromBranch'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequisitionId'])->references(['Id'])->on('t_InterBranchRequisition')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ToBranch'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TransferredBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->dropForeign('t_transfers_createdby_foreign');
            $table->dropForeign('t_transfers_deletedby_foreign');
            $table->dropForeign('t_transfers_frombranch_foreign');
            $table->dropForeign('t_transfers_modifiedby_foreign');
            $table->dropForeign('t_transfers_requisitionid_foreign');
            $table->dropForeign('t_transfers_tobranch_foreign');
            $table->dropForeign('t_transfers_transferredby_foreign');
        });
    }
};
