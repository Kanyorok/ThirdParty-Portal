<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('t_Tenders', function (Blueprint $table) {
            // Drop existing foreign key constraint
            $table->dropForeign(['RelatedPRID']);
        });

        Schema::table('t_Tenders', function (Blueprint $table) {
            // Make the column nullable and re-add foreign key
            //$table->string('RelatedPRID')->nullable();
        });
    }

    public function down()
    {
        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->dropForeign(['RelatedPRID']);
        });

        Schema::table('t_Tenders', function (Blueprint $table) {
            $table->foreignId('RelatedPRID')->nullable(false)->change();
            $table->foreign('RelatedPRID')->references('Id')->on('t_Requisitions');
        });
    }
};
