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
        Schema::table('t_PropertyBlock', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PropertyID'])->references(['Id'])->on('t_PropertyRegistry')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PropertyBlock', function (Blueprint $table) {
            $table->dropForeign('t_propertyblock_createdby_foreign');
            $table->dropForeign('t_propertyblock_deletedby_foreign');
            $table->dropForeign('t_propertyblock_modifiedby_foreign');
            $table->dropForeign('t_propertyblock_propertyid_foreign');
        });
    }
};
