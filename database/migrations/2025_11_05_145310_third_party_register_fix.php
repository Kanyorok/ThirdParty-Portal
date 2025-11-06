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
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->dropUnique(['RegistrationNumber']);
            $table->dropColumn('Country');
        });
    }

    public function down(): void
    {
        Schema::table('t_ThirdParties', function (Blueprint $table) {
            $table->unique('RegistrationNumber');
            $table->string('Country')->default('1');
        });
    }
};
