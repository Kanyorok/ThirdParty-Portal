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
        Schema::table('t_MarketingListsFilters', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['FilterId'])->references(['Id'])->on('t_SysFilters')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['MarketingListId'])->references(['MarketingListID'])->on('t_MarketingLists')->onUpdate('cascade')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_MarketingListsFilters', function (Blueprint $table) {
            $table->dropForeign('t_marketinglistsfilters_createdby_foreign');
            $table->dropForeign('t_marketinglistsfilters_deletedby_foreign');
            $table->dropForeign('t_marketinglistsfilters_filterid_foreign');
            $table->dropForeign('t_marketinglistsfilters_marketinglistid_foreign');
            $table->dropForeign('t_marketinglistsfilters_modifiedby_foreign');
        });
    }
};
