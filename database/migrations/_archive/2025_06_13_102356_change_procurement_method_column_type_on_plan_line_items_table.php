<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            $table->unsignedBigInteger('ProcurementMethod')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            $table->string('ProcurementMethod')->nullable()->change();
        });
    }
};

