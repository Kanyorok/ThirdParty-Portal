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
        Schema::table('t_InterBranchRequisition', function (Blueprint $table) {

            if (Schema::hasColumn('t_InterBranchRequisition', 'ItemCode')) {
                $table->dropConstrainedForeignId('ItemCode'); // Drops the foreign key and the column
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'ItemName')) {
                $table->dropConstrainedForeignId('ItemName'); // Drops the foreign key and the column
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'UOM')) {
                $table->dropConstrainedForeignId('UOM'); // Drops the foreign key and the column
            }
            if (Schema::hasColumn('t_InterBranchRequisition', 'Requested Qty')) {
                $table->dropColumn('Requested Qty');
            }

            if (Schema::hasColumn('t_InterBranchRequisition', 'Remarks')) {
                $table->dropColumn('Remarks');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_InterBranchRequisition', function (Blueprint $table) {
            $table->foreignId('ItemCode')->constrained('t_Items', 'Id');
            $table->foreignId('ItemName')->constrained('t_Items', 'Id');
            $table->foreignId('UOM')->constrained('t_Items', 'Id');
            $table->integer('Requested Qty');
            $table->string('Remarks');
        });
    }
};

