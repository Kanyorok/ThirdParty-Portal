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
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->string('CategoryCode')->nullable(false);
            $table->boolean('Status')->default(true)->comment('Category status: Active or Inactive');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_ItemCategories', function (Blueprint $table) {
            $table->dropColumn(['CategoryCode', 'Status']);
        });
    }
};
