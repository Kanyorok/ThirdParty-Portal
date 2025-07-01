<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->dropForeign(['FromBranch']);
            $table->foreignId('FromBranch')->nullable()->change();
            $table->foreign('FromBranch')->references('Id')->on('t_Branches');
        });
    }

    public function down(): void
    {
        Schema::table('t_Transfers', function (Blueprint $table) {
            $table->dropForeign(['FromBranch']);
            $table->foreignId('FromBranch')->nullable(false)->change();
            $table->foreign('FromBranch')->references('Id')->on('t_Branches');
        });
    }
};
