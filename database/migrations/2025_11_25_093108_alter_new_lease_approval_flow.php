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
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->string('ApprovalStatus')->nullable()->after('Status');
            $table->boolean('IsOfferGenerated')->default(false)->after('ApprovalStatus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_LeaseCreation', function (Blueprint $table) {
            $table->dropColumn('ApprovalStatus');
            $table->dropColumn('IsOfferGenerated');
        });
    }
};
