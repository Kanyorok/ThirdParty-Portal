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
        Schema::create('t_DashboardWidgets', function (Blueprint $table) {
            $table->bigIncrements('Id');
            $table->string('Key')->unique();
            $table->string('Name');
            $table->string('Description')->nullable();
            $table->string('View');
            $table->smallInteger('DefaultW')->default(6);
            $table->smallInteger('DefaultH')->default(1);
            $table->boolean('IsActive')->default(true);
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();
            $table->string('Module', 100)->nullable();
            $table->string('Type', 50)->nullable();
            $table->string('DataEndpoint')->nullable();
            $table->text('DefaultFilters')->nullable();

            $table->primary(['Id'], 'pk__t_dashbo__3214ec07de178337');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_DashboardWidgets');
    }
};
