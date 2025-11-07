<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->string('RequisitionType')
                ->nullable()
                ->after('RequisitionId')
                ->comment('Either procurement or interbranch');
        });
    }

    public function down(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->dropColumn('RequisitionType');
        });
    }
};
