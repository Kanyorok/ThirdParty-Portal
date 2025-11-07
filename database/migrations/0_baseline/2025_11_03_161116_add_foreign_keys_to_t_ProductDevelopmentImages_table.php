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
        Schema::table('t_ProductDevelopmentImages', function (Blueprint $table) {
            $table->foreign(['CreatedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ModifiedBy'])->references(['Id'])->on('t_Users')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ProductDevelopmentId'])->references(['Id'])->on('t_ProductDevelopment')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ProductDevelopmentImages', function (Blueprint $table) {
            $table->dropForeign('t_productdevelopmentimages_createdby_foreign');
            $table->dropForeign('t_productdevelopmentimages_imageid_foreign');
            $table->dropForeign('t_productdevelopmentimages_modifiedby_foreign');
            $table->dropForeign('t_productdevelopmentimages_productdevelopmentid_foreign');
        });
    }
};
