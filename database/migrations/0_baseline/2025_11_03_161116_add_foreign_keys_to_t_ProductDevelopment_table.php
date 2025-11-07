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
        Schema::table('t_ProductDevelopment', function (Blueprint $table) {
            $table->foreign(['ArchivedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['StageId'])->references(['ID'])->on('t_CodeDetails')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ProductDevelopment', function (Blueprint $table) {
            $table->dropForeign('t_productdevelopment_archivedby_foreign');
            $table->dropForeign('t_productdevelopment_createdby_foreign');
            $table->dropForeign('t_productdevelopment_deletedby_foreign');
            $table->dropForeign('t_productdevelopment_modifiedby_foreign');
            $table->dropForeign('t_productdevelopment_stageid_foreign');
        });
    }
};
