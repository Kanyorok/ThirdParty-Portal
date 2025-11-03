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
        Schema::table('t_PropertyRegistry', function (Blueprint $table) {
            $table->foreign(['Category'])->references(['Id'])->on('t_CategoryMaster')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CountryId'])->references(['Id'])->on('t_Countries')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['DeletedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['LocationId'])->references(['ID'])->on('t_Localities')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['PropertyType'])->references(['Id'])->on('t_PropertyType')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_PropertyRegistry', function (Blueprint $table) {
            $table->dropForeign('t_propertyregistry_category_foreign');
            $table->dropForeign('t_propertyregistry_countryid_foreign');
            $table->dropForeign('t_propertyregistry_createdby_foreign');
            $table->dropForeign('t_propertyregistry_deletedby_foreign');
            $table->dropForeign('t_propertyregistry_locationid_foreign');
            $table->dropForeign('t_propertyregistry_modifiedby_foreign');
            $table->dropForeign('t_propertyregistry_propertytype_foreign');
        });
    }
};
