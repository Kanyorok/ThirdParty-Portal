<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('t_Calls', static function (Blueprint $table) {
            $table->string("Source", 100)->nullable();
            $table->string("SourceID", 100)->nullable();

            $table->index(["Source", "SourceID"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Calls', static function (Blueprint $table) {
            $table->dropIndex(['Source', 'SourceID']);
            $table->dropColumn(['Source', 'SourceID']);
        });
    }
};
