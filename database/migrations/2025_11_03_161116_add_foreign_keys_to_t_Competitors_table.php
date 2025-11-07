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
        Schema::table('t_Competitors', function (Blueprint $table) {
            $table->foreign(['CountryId'])->references(['Id'])->on('t_Countries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LocationID'])->references(['ID'])->on('t_Localities')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['Logo'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Competitors', function (Blueprint $table) {
            $table->dropForeign('t_competitors_countryid_foreign');
            $table->dropForeign('t_competitors_createdby_foreign');
            $table->dropForeign('t_competitors_deletedby_foreign');
            $table->dropForeign('t_competitors_locationid_foreign');
            $table->dropForeign('t_competitors_logo_foreign');
            $table->dropForeign('t_competitors_modifiedby_foreign');
        });
    }
};
