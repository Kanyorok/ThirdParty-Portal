<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_PettyCashVouchers', function (Blueprint $table) {
            $table->string('ApprovalStatus', 20)->default('N/A'); // N/A|Pending|Approved|Rejected
            $table->dateTime('SubmittedOn')->nullable();
            $table->unsignedBigInteger('SubmittedBy')->nullable();
            $table->dateTime('ApprovedOn')->nullable();
            $table->unsignedBigInteger('ApprovedBy')->nullable();

            $table->unsignedBigInteger('ReplenishmentBatchID')->nullable();
            $table->index(['ReplenishmentBatchID']);
        });
    }

    public function down(): void
    {
        Schema::table('t_PettyCashVouchers', function (Blueprint $table) {
            $table->dropColumn(['ApprovalStatus', 'SubmittedOn', 'SubmittedBy', 'ApprovedOn', 'ApprovedBy', 'ReplenishmentBatchID']);
        });
    }
};
