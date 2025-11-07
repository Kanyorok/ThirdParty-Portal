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
        Schema::table('t_CategoryProgressHistory', function (Blueprint $table) {
            $table->foreign(['ApplicationCategoryId'], 'FK_ProgHist_AppCat')->references(['Id'])->on('t_ApplicationCategoryStatus')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ChangedBy'], 'FK_ProgHist_ChangedBy')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'], 'FK_ProgHist_CreatedBy')->references(['Id'])->on('t_ThirdPartyUsers')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CategoryProgressHistory', function (Blueprint $table) {
            $table->dropForeign('FK_ProgHist_AppCat');
            $table->dropForeign('FK_ProgHist_ChangedBy');
            $table->dropForeign('FK_ProgHist_CreatedBy');
        });
    }
};
