<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoveRequisitionidToRfqLines extends Migration
{
    public function up(): void
    {
        // Remove foreign key and column from t_RFQ
        Schema::table('t_RFQ', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('RequisitionId');
        });

        // Add foreign key to t_RFQLines
        Schema::table('t_RFQLines', static function (Blueprint $table) {
            $table->foreignId('RequisitionId')->constrained('t_Requisitions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse: Add back to t_RFQ
        Schema::table('t_RFQ', static function (Blueprint $table) {
            $table->foreignId('RequisitionId')->nullable()->constrained('t_Requisitions');
        });

        // Remove from t_RFQLines
        Schema::table('t_RFQLines', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('RequisitionId');
        });
    }
}
