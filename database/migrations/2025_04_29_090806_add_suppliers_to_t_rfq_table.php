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
        Schema::table('t_RFQ', function (Blueprint $table) {
            $table->json('Suppliers')->nullable(); // nullable is optional
        });
    }

    public function down()
    {
        Schema::table('t_RFQ', function (Blueprint $table) {
            $table->dropColumn('Suppliers');
        });
    }
};
