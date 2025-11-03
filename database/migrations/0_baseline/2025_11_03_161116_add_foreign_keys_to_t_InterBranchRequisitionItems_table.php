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
        Schema::table('t_InterBranchRequisitionItems', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Item'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['RequisitionId'])->references(['Id'])->on('t_InterBranchRequisition')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_InterBranchRequisitionItems', function (Blueprint $table) {
            $table->dropForeign('t_interbranchrequisitionitems_createdby_foreign');
            $table->dropForeign('t_interbranchrequisitionitems_deletedby_foreign');
            $table->dropForeign('t_interbranchrequisitionitems_item_foreign');
            $table->dropForeign('t_interbranchrequisitionitems_modifiedby_foreign');
            $table->dropForeign('t_interbranchrequisitionitems_requisitionid_foreign');
        });
    }
};
