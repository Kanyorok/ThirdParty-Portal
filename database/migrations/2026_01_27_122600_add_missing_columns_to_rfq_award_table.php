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
        Schema::table('t_RFQAward', function (Blueprint $table) {
            
            if (!Schema::hasColumn('t_RFQAward', 'AwardJustification')) {
                $table->text('AwardJustification')->nullable()->after('AwardedAmount');
            }

            if (!Schema::hasColumn('t_RFQAward', 'ApprovalRemarks')) {
                $table->text('ApprovalRemarks')->nullable()->after('AwardJustification');
            }

            if (!Schema::hasColumn('t_RFQAward', 'ApprovedBy')) {
                $table->foreignId('ApprovedBy')->nullable()->constrained('t_Users', 'Id')->after('ApprovalRemarks');
            }

            if (!Schema::hasColumn('t_RFQAward', 'ApprovedOn')) {
                $table->dateTime('ApprovedOn')->nullable()->after('ApprovedBy');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQAward', function (Blueprint $table) {
            if (Schema::hasColumn('t_RFQAward', 'ApprovedBy')) {
                 $table->dropForeign(['ApprovedBy']);
                 $table->dropColumn('ApprovedBy');
            }
            if (Schema::hasColumn('t_RFQAward', 'ApprovedOn')) {
                $table->dropColumn('ApprovedOn');
            }
            if (Schema::hasColumn('t_RFQAward', 'ApprovalRemarks')) {
                $table->dropColumn('ApprovalRemarks');
            }
            if (Schema::hasColumn('t_RFQAward', 'AwardJustification')) {
                 $table->dropColumn('AwardJustification');
            }
        });
    }
};
