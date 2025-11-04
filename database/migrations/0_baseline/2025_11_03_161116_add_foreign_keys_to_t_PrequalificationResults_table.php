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
        Schema::table('t_PrequalificationResults', function (Blueprint $table) {
            $table->foreign(['ApplicationID'])->references(['ApplicationID'])->on('t_SupplierPrequalificationApplications')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ApprovalBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PrequalificationResults', function (Blueprint $table) {
            $table->dropForeign('t_prequalificationresults_applicationid_foreign');
            $table->dropForeign('t_prequalificationresults_approvalby_foreign');
        });
    }
};
