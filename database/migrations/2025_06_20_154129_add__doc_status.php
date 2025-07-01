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
        Schema::table('t_Requisitions', function (Blueprint $table) {
            $table->char('DocStatus', 2)->nullable()->default('p')->after('PlanRef')
                ->comment('Document Status: d - draft, p-pending, a - approved, r - rejected, c - cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_Requisitions', function (Blueprint $table) {
            $table->dropColumn('DocStatus');
        });
    }
};
