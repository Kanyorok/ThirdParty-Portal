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
        Schema::table('t_UOMConversions', function (Blueprint $table) {
            $table->foreign(['AlternateUOM'])->references(['Id'])->on('t_UOM')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Item'])->references(['Id'])->on('t_Items')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['UOM'])->references(['Id'])->on('t_UOM')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_UOMConversions', function (Blueprint $table) {
            $table->dropForeign('t_uomconversions_alternateuom_foreign');
            $table->dropForeign('t_uomconversions_createdby_foreign');
            $table->dropForeign('t_uomconversions_deletedby_foreign');
            $table->dropForeign('t_uomconversions_item_foreign');
            $table->dropForeign('t_uomconversions_modifiedby_foreign');
            $table->dropForeign('t_uomconversions_uom_foreign');
        });
    }
};
