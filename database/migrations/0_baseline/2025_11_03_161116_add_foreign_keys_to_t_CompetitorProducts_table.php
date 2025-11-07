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
        Schema::table('t_CompetitorProducts', function (Blueprint $table) {
            $table->foreign(['CompetitorId'])->references(['CompetitorID'])->on('t_Competitors')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_CompetitorProducts', function (Blueprint $table) {
            $table->dropForeign('t_competitorproducts_competitorid_foreign');
            $table->dropForeign('t_competitorproducts_createdby_foreign');
            $table->dropForeign('t_competitorproducts_deletedby_foreign');
            $table->dropForeign('t_competitorproducts_modifiedby_foreign');
        });
    }
};
