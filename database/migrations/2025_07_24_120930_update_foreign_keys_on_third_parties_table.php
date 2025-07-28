<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateForeignKeysOnThirdPartiesTable extends Migration
{
    public function up(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            // Drop existing FKs if needed
            $table->dropForeign(['CreatedBy']);
            $table->dropForeign(['ModifiedBy']);
            $table->dropForeign(['DeletedBy']);

            // Re-add with correct constraints
            $table->foreign('CreatedBy')
                ->references('Id')
                ->on('t_ThirdPartyUsers')
                ->onDelete('SET NULL'); // only this one cascades

            $table->foreign('ModifiedBy')
                ->references('Id')
                ->on('t_ThirdPartyUsers')
                ->onDelete('NO ACTION'); // safe

            $table->foreign('DeletedBy')
                ->references('Id')
                ->on('t_ThirdPartyUsers')
                ->onDelete('NO ACTION'); // safe
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
