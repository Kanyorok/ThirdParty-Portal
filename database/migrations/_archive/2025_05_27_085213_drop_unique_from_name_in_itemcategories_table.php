<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->dropUnique('t_ItemCategories_Name_unique'); // Adjust index name if necessary
        });
    }

    public function down(): void
    {
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->unique('Name');
        });
    }
};
