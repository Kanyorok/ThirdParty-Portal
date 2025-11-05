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
        Schema::table('t_BudgetGLMasterAllocations', function (Blueprint $table) {
            $table->foreign(['BranchID'])->references(['Id'])->on('t_Branches')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['BudgetID'])->references(['Id'])->on('t_Budgets')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['GLAttachmentID'])->references(['Id'])->on('t_BudgetGLsAttachments')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['IsBeingEditedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BudgetGLMasterAllocations', function (Blueprint $table) {
            $table->dropForeign('t_budgetglmasterallocations_branchid_foreign');
            $table->dropForeign('t_budgetglmasterallocations_budgetid_foreign');
            $table->dropForeign('t_budgetglmasterallocations_createdby_foreign');
            $table->dropForeign('t_budgetglmasterallocations_deletedby_foreign');
            $table->dropForeign('t_budgetglmasterallocations_glattachmentid_foreign');
            $table->dropForeign('t_budgetglmasterallocations_isbeingeditedby_foreign');
            $table->dropForeign('t_budgetglmasterallocations_modifiedby_foreign');
        });
    }
};
