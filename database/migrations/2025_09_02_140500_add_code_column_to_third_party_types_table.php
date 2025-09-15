<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_ThirdPartyTypes', function (Blueprint $table) {
            if (!Schema::hasColumn('t_ThirdPartyTypes', 'Code')) {
                $table->string('Code', 30)->nullable()->unique()->after('TypeId');
            }
        });
    }

    public function down(): void
    {
        Schema::table('t_ThirdPartyTypes', function (Blueprint $table) {
            if (Schema::hasColumn('t_ThirdPartyTypes', 'Code')) {
                $table->dropUnique(['Code']);
                $table->dropColumn('Code');
            }
        });
    }
};