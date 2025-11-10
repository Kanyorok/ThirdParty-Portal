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
        Schema::table('t_RequisitionLines', static function (Blueprint $table) {
            $table->dropColumn(['NeededBy', 'RequisitionID']);
        });

        Schema::table('t_RequisitionLines', static function (Blueprint $table) {
            $table->foreignId('RequisitionID')->nullable()->constrained('t_Requisitions', 'Id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('RequisitionID');
        });
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
            $table->integer('RequisitionID')->nullable();
            $table->date('NeededBy')->nullable();
        });
    }
};
