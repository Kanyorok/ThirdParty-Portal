<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('t_DashboardWidgets')) {
            Schema::table('t_DashboardWidgets', function (Blueprint $table) {
                if (!Schema::hasColumn('t_DashboardWidgets', 'Module')) {
                    $table->string('Module', 100)->nullable()->after('Name');
                }
                if (!Schema::hasColumn('t_DashboardWidgets', 'Type')) {
                    $table->string('Type', 50)->nullable()->after('Module');
                }
                if (!Schema::hasColumn('t_DashboardWidgets', 'DataEndpoint')) {
                    $table->string('DataEndpoint')->nullable()->after('View');
                }
                if (!Schema::hasColumn('t_DashboardWidgets', 'DefaultFilters')) {
                    $table->json('DefaultFilters')->nullable()->after('DataEndpoint');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_DashboardWidgets')) {
            Schema::table('t_DashboardWidgets', function (Blueprint $table) {
                if (Schema::hasColumn('t_DashboardWidgets', 'DefaultFilters')) {
                    $table->dropColumn('DefaultFilters');
                }
                if (Schema::hasColumn('t_DashboardWidgets', 'DataEndpoint')) {
                    $table->dropColumn('DataEndpoint');
                }
                if (Schema::hasColumn('t_DashboardWidgets', 'Type')) {
                    $table->dropColumn('Type');
                }
                if (Schema::hasColumn('t_DashboardWidgets', 'Module')) {
                    $table->dropColumn('Module');
                }
            });
        }
    }
};
