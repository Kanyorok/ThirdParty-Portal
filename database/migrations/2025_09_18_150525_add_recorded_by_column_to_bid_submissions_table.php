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
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->string('RecordedBy', 255)->nullable()->after('ReceivedAt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_BidSubmissions', function (Blueprint $table) {
            $table->dropColumn('RecordedBy');
        });
    }
};