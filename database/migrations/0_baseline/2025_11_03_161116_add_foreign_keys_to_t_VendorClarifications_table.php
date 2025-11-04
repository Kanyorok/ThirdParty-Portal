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
        Schema::table('t_VendorClarifications', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['TenderID'])->references(['Id'])->on('t_Tenders')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['VendorID'])->references(['Id'])->on('t_Suppliers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_VendorClarifications', function (Blueprint $table) {
            $table->dropForeign('t_vendorclarifications_createdby_foreign');
            $table->dropForeign('t_vendorclarifications_deletedby_foreign');
            $table->dropForeign('t_vendorclarifications_modifiedby_foreign');
            $table->dropForeign('t_vendorclarifications_tenderid_foreign');
            $table->dropForeign('t_vendorclarifications_vendorid_foreign');
        });
    }
};
