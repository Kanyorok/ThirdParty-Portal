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
        Schema::table('t_ThirdParties', static function (Blueprint $table) {
            $table->string('IDNumber')->nullable();
            $table->string('PassportNo')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ThirdParties', static function (Blueprint $table) {
            $table->dropColumn('IDNumber');
            $table->dropColumn('PassportNo');
            }
        );
    }
};
