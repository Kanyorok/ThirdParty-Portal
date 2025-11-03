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
        Schema::table('t_BancassuranceClaims ', function (Blueprint $table) {
            $table->foreign(['ClaimType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PolicyId'])->references(['Id'])->on('t_BancassurancePolicies')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BancassuranceClaims ', function (Blueprint $table) {
            $table->dropForeign('t_bancassuranceclaims _claimtype_foreign');
            $table->dropForeign('t_bancassuranceclaims _createdby_foreign');
            $table->dropForeign('t_bancassuranceclaims _deletedby_foreign');
            $table->dropForeign('t_bancassuranceclaims _modifiedby_foreign');
            $table->dropForeign('t_bancassuranceclaims _policyid_foreign');
        });
    }
};
