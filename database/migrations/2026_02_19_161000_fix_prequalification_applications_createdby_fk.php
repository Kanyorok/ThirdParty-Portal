<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix incorrect FK references on t_SupplierPrequalificationApplications.
 *
 * The original migration pointed CreatedBy / ModifiedBy / DeletedBy at
 * t_ThirdPartyUsers, but these columns are populated by internal staff
 * users stored in t_Users.  Drop the wrong constraints and re‑add them
 * pointing at t_Users.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            // Drop the three incorrect FK constraints
            $table->dropForeign(['CreatedBy']);
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);
        });

        // Fix existing data that doesn't map to t_Users
        $firstUserId = \Illuminate\Support\Facades\DB::table('t_Users')->orderBy('Id')->value('Id');
        if ($firstUserId) {
            \Illuminate\Support\Facades\DB::table('t_SupplierPrequalificationApplications')
                ->whereNotIn('CreatedBy', function($query) {
                    $query->select('Id')->from('t_Users');
                })
                ->update(['CreatedBy' => $firstUserId]);

            \Illuminate\Support\Facades\DB::table('t_SupplierPrequalificationApplications')
                ->whereNotNull('ModifiedBy')
                ->whereNotIn('ModifiedBy', function($query) {
                    $query->select('Id')->from('t_Users');
                })
                ->update(['ModifiedBy' => $firstUserId]);

            \Illuminate\Support\Facades\DB::table('t_SupplierPrequalificationApplications')
                ->whereNotNull('DeletedBy')
                ->whereNotIn('DeletedBy', function($query) {
                    $query->select('Id')->from('t_Users');
                })
                ->update(['DeletedBy' => $firstUserId]);
        }

        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            // Re-add pointing at t_Users.Id
            $table->foreign('CreatedBy')->references('Id')->on('t_Users');
            $table->foreign('ModifiedBy')->references('Id')->on('t_Users')->nullable();
            $table->foreign('DeletedBy')->references('Id')->on('t_Users')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('t_SupplierPrequalificationApplications', function (Blueprint $table) {
            // Restore original (incorrect) FKs
            $table->dropForeign(['CreatedBy']);
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);

            $table->foreign('CreatedBy')->references('Id')->on('t_ThirdPartyUsers');
            $table->foreign('ModifiedBy')->references('Id')->on('t_ThirdPartyUsers')->nullable();
            $table->foreign('DeletedBy')->references('Id')->on('t_ThirdPartyUsers')->nullable();
        });
    }
};
