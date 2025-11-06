<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_PettyCashFloats', function (Blueprint $table) {
            $table->boolean('RequireApproval')->default(0);
            $table->decimal('ApprovalLimit', 18, 2)->default(0); // amount above which approval is required
        });
    }

    public function down(): void
    {
        Schema::table('t_PettyCashFloats', function (Blueprint $table) {
            $table->dropColumn(['RequireApproval', 'ApprovalLimit']);
        });
    }
};
