<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_ItemCategories', function (Blueprint $table) {

            $table->string('CategoryCode')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->dropUnique(['CategoryCode']);
            $table->string('CategoryCode')->nullable()->change();
        });
    }
};
