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
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->unsignedBigInteger('SupplierId')->nullable()->after('id'); // or after any specific column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->dropColumn('SupplierId');
        });
    }
};
