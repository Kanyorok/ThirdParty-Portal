<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('t_Tenders', function (Blueprint $table) {
        $table->unsignedBigInteger('RelatedPRID')->nullable()->change();
    });
}
 
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->unsignedBigInteger('RelatedPRID')->nullable(false)->change();
        });
    }
};
