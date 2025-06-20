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
            $table->integer('OriginalQTY')->nullable()->after('MergedQty');
        });
    }

    public function down()
    {
        Schema::table('t_PlanLineItem', function (Blueprint $table) {
            $table->dropColumn('OriginalQTY');
        });
    }
};
