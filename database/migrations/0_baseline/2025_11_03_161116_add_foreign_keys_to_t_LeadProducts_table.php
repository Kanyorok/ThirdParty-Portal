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
        Schema::table('t_LeadProducts', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LeadId'])->references(['LeadID'])->on('t_Leads')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_LeadProducts', function (Blueprint $table) {
            $table->dropForeign('t_leadproducts_createdby_foreign');
            $table->dropForeign('t_leadproducts_deletedby_foreign');
            $table->dropForeign('t_leadproducts_leadid_foreign');
            $table->dropForeign('t_leadproducts_modifiedby_foreign');
        });
    }
};
