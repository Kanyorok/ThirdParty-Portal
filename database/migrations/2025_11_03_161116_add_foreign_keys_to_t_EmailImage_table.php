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
        Schema::table('t_EmailImage', function (Blueprint $table) {
            $table->foreign(['EmailId'])->references(['EmailID'])->on('t_Emails')->onUpdate('no action')->onDelete('no action');
            $table->foreign(['ImageId'])->references(['ImageID'])->on('t_Images')->onUpdate('no action')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_EmailImage', function (Blueprint $table) {
            $table->dropForeign('t_emailimage_emailid_foreign');
            $table->dropForeign('t_emailimage_imageid_foreign');
        });
    }
};
