<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRKPIItems', function (Blueprint $table) {
            $table->string('Perspective', 150)->nullable()->after('Unit');
            $table->unsignedBigInteger('PerspectiveID')->nullable()->after('Perspective');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRKPIItems', function (Blueprint $table) {
            $table->dropColumn(['Perspective', 'PerspectiveID']);
        });
    }
};
