<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_InterBranchRequisition', function (Blueprint $table) {
            if (Schema::hasColumn('t_InterBranchRequisition', 'ItemCode')) {
                $table->dropConstrainedForeignId('ItemCode');
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'ItemName')) {
                $table->dropConstrainedForeignId('ItemName');
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'UOM')) {
                $table->dropConstrainedForeignId('UOM');
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'Requested Qty')) {
                $table->dropColumn('Requested Qty');
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'Remarks')) {
                $table->dropColumn('Remarks');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_InterBranchRequisition', function (Blueprint $table) {

            $table->foreignId('ItemCode')->nullable()->constrained('t_Items', 'Id');
            $table->foreignId('ItemName')->nullable()->constrained('t_Items', 'Id');
            $table->foreignId('UOM')->nullable()->constrained('t_Items', 'Id');
            $table->integer('RequestedQty')->nullable(); 
            $table->string('Remarks')->nullable();
        });
    }
};