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
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            $table->foreign(['Block'])->references(['Id'])->on('t_PropertyBlock')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Floor'])->references(['Id'])->on('t_PropertyFloor')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['IssueType'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Priority'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Property'])->references(['Id'])->on('t_PropertyRegistry')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Unit'])->references(['Id'])->on('t_PropertyUnit')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MaintenanceRequest', function (Blueprint $table) {
            $table->dropForeign('t_maintenancerequest_block_foreign');
            $table->dropForeign('t_maintenancerequest_createdby_foreign');
            $table->dropForeign('t_maintenancerequest_deletedby_foreign');
            $table->dropForeign('t_maintenancerequest_floor_foreign');
            $table->dropForeign('t_maintenancerequest_issuetype_foreign');
            $table->dropForeign('t_maintenancerequest_modifiedby_foreign');
            $table->dropForeign('t_maintenancerequest_priority_foreign');
            $table->dropForeign('t_maintenancerequest_property_foreign');
            $table->dropForeign('t_maintenancerequest_unit_foreign');
        });
    }
};
