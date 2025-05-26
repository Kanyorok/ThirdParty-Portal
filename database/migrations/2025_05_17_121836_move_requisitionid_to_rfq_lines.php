<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveRequisitionidToRfqLines extends Migration
{
    public function up()
    {
        // Remove foreign key and column from t_RFQ
        Schema::table('t_RFQ', function (Blueprint $table) {
            $table->dropForeign(['RequisitionId']);
            $table->dropColumn('RequisitionId');
        });

        // Add foreign key to t_RFQLines
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->foreignId('RequisitionId')
                ->constrained('t_Requisitions')
                ->cascadeOnDelete(); // Optional: use depending on your FK policy
        });
    }

    public function down()
    {
        // Reverse: Add back to t_RFQ
        Schema::table('t_RFQ', function (Blueprint $table) {
            $table->foreignId('RequisitionId')
                ->constrained('t_Requisitions')
                ->cascadeOnDelete(); // match your original constraint
        });

        // Remove from t_RFQLines
        Schema::table('t_RFQLines', function (Blueprint $table) {
            $table->dropForeign(['RequisitionId']);
            $table->dropColumn('RequisitionId');
        });
    }
}
