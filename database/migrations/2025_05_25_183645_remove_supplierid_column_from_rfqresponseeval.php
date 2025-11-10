<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_RFQEvaluations', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('SupplierId');
        });
    }

    public function down(): void
    {
        Schema::table('t_RFQEvaluations', static function (Blueprint $table) {
            $table->foreignId('SupplierId')->nullable()->constrained('t_Suppliers');
        });
    }
};
