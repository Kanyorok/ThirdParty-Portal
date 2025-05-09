<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            $table->dropUnique(['RFQNumber']); // remove unique constraint
        });
    }

    public function down(): void
    {
        Schema::table('t_RFQResponse', function (Blueprint $table) {
            $table->unique('RFQNumber'); // rollback by reapplying unique
        });
    }
};
