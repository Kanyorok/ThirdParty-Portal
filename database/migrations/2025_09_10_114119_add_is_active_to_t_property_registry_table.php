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
        Schema::table('t_PropertyRegistry', function (Blueprint $table) {
            $table->boolean('IsActive')->default(true)->after('PropertyDescription'); 
        });
    }

    public function down(): void
    {
        Schema::table('t_PropertyRegistry', function (Blueprint $table) {
            $table->dropColumn('IsActive');
        });
    }
};
