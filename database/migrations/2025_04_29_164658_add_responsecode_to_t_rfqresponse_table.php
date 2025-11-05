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
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            $table->string('RFQResponseNumber')->unique()->comment('Unique identifier for the RFQ');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            $table->dropUnique('t_rfqresponse_rfqresponsenumber_unique');
            $table->dropColumn('RFQResponseNumber');
        });
    }
};
