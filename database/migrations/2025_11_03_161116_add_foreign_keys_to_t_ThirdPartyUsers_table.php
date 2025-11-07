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
        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->foreign(['ThirdPartyId'], 'fk_t_thirdpartyusers_thirdpartyid')->references(['Id'])->on('t_ThirdParties')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdPartyUsers', function (Blueprint $table) {
            $table->dropForeign('fk_t_thirdpartyusers_thirdpartyid');
            $table->dropForeign('t_thirdpartyusers_createdby_foreign');
            $table->dropForeign('t_thirdpartyusers_deletedby_foreign');
            $table->dropForeign('t_thirdpartyusers_modifiedby_foreign');
        });
    }
};
