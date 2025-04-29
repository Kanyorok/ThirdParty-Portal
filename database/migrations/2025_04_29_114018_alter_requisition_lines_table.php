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
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
//            $table->dropColumn(['Module', 'ExpectedPrice']);           
            $table->date('NeededBy')->nullable();
            $table->decimal('ExpectedPrice')->nullable();
            $table->unsignedBigInteger('RequisitionID')->nullable()->change(); // make sure it's nullable
            $table->foreign('RequisitionID')->references('Id')->on('t_Requisitions')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RequisitionLines', function (Blueprint $table) {
//            $table->decimal('ExpectedPrice', 15, 2)->nullable();
            $table->dropForeign(['RequisitionID']);
            $table->dropColumn('NeededBy');
        });
    }
};
