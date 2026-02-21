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
        Schema::table('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->boolean('IsInterbranchGL')->default(false);
            $table->string('InterbranchRole', 100)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_FinanceGLAccounts', function (Blueprint $table) {
            $table->dropColumn('IsInterbranchGL');
            $table->dropColumn('InterbranchRole');
        });
    }
};
