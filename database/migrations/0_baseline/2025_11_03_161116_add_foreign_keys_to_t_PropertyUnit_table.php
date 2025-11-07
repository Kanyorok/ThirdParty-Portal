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
        Schema::table('t_PropertyUnit', function (Blueprint $table) {
            $table->foreign(['BlockID'])->references(['Id'])->on('t_PropertyBlock')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FloorID'])->references(['Id'])->on('t_PropertyFloor')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PropertyID'])->references(['Id'])->on('t_PropertyRegistry')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PropertyUnit', function (Blueprint $table) {
            $table->dropForeign('t_propertyunit_blockid_foreign');
            $table->dropForeign('t_propertyunit_createdby_foreign');
            $table->dropForeign('t_propertyunit_deletedby_foreign');
            $table->dropForeign('t_propertyunit_floorid_foreign');
            $table->dropForeign('t_propertyunit_modifiedby_foreign');
            $table->dropForeign('t_propertyunit_propertyid_foreign');
        });
    }
};
