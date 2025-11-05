<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('t_DashboardWidgets')) {
            Schema::create('t_DashboardWidgets', function (Blueprint $table) {
                $table->id('Id');
                $table->string('Key')->unique();
                $table->string('Name');
                $table->string('Description')->nullable();
                // Blade view path to render this widget
                $table->string('View');
                // Default size and positioning hints
                $table->unsignedSmallInteger('DefaultW')->default(6); // bootstrap-ish cols
                $table->unsignedSmallInteger('DefaultH')->default(1);
                $table->boolean('IsActive')->default(true);
                $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
                $table->dateTime('CreatedOn');
                $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
                $table->dateTime('ModifiedOn');
                $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
                $table->softDeletes('DeletedOn');
            });
        }

        if (!Schema::hasTable('user_dashboard_widgets')) {
            Schema::create('user_dashboard_widgets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('widget_key');
                // Grid position and size
                $table->unsignedInteger('x')->default(0);
                $table->unsignedInteger('y')->default(0);
                $table->unsignedSmallInteger('w')->default(6);
                $table->unsignedSmallInteger('h')->default(1);
                $table->unsignedInteger('sort_order')->default(0);
                $table->json('config')->nullable();
                $table->timestamps();

                $table->index(['user_id']);
                $table->index(['widget_key']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_widgets');
    Schema::dropIfExists('t_DashboardWidgets');
    }
};
