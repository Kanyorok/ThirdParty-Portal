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
        Schema::table('t_RFQEvaluation', function (Blueprint $table) {
            $table->string('CommitteeMemberName')->nullable()->after('RFQEvaluateNumber'); // replace 'some_existing_column' with the actual one
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            $table->dropColumn('CommitteeMemberName');
        });
    }
};
