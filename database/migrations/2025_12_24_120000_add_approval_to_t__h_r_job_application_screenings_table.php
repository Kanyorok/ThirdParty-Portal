<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_HRJobApplicationScreenings', function (Blueprint $table) {
            $table->string('ApprovalStatus', 30)->nullable()->after('Status');
            $table->unsignedBigInteger('ApprovedBy')->nullable()->after('ApprovalStatus');
            $table->dateTime('ApprovedOn')->nullable()->after('ApprovedBy');
        });
    }

    public function down(): void
    {
        Schema::table('t_HRJobApplicationScreenings', function (Blueprint $table) {
            $table->dropColumn(['ApprovalStatus', 'ApprovedBy', 'ApprovedOn']);
        });
    }
};
