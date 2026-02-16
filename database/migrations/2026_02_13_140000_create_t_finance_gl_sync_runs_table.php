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
        Schema::create('t_FinanceGLSyncRuns', function (Blueprint $table) {
            $table->id('Id');
            $table->string('Source', 50)->default('NIMBLE');
            $table->string('Status', 20)->default('pending');
            $table->string('Message', 500)->nullable();
            $table->text('SyncError')->nullable();
            $table->integer('RecordsSynced')->default(0);
            $table->integer('RecordsFailed')->default(0);
            $table->integer('TotalRecords')->nullable();
            $table->integer('CurrentPage')->default(0);
            $table->integer('PageSize')->default(1000);
            $table->string('LastCursor', 255)->nullable();
            $table->dateTime('StartedAt')->nullable();
            $table->dateTime('CompletedAt')->nullable();

            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_FinanceGLSyncRuns');
    }
};
