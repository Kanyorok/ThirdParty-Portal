<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeysOnThirdPartiesTable extends Migration
{
    public function up(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->dropForeign(['CreatedBy']);
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);

            $table->foreign('CreatedBy')
                ->references('Id')
                ->on('t_ThirdPartyUsers');

            $table->foreign('ModifiedBy')
                ->references('Id')
                ->on('t_ThirdPartyUsers');

            $table->foreign('DeletedBy')
                ->references('Id')
                ->on('t_ThirdPartyUsers');
        });
    }

    public function down(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->dropForeign(['CreatedBy']);
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);
        });
    }
}
