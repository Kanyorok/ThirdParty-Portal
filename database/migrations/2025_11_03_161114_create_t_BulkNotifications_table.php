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
        Schema::create('t_BulkNotifications', function (Blueprint $table) {
            $table->bigIncrements('BulkNotificationID');
            $table->string('Label')->nullable();
            $table->string('Module')->index();
            $table->text('Title')->nullable();
            $table->text('Content');
            $table->text('Extra')->nullable();
            $table->dateTime('CompleteOn')->nullable();
            $table->bigInteger('Total');
            $table->bigInteger('CreatedBy');
            $table->dateTime('CreatedOn');
            $table->bigInteger('ModifiedBy');
            $table->dateTime('ModifiedOn');
            $table->bigInteger('DeletedBy')->nullable();
            $table->dateTime('DeletedOn')->nullable();

            $table->primary(['BulkNotificationID'], 'pk__t_bulkno__e7e54ed9b80d54a6');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('t_BulkNotifications');
    }
};
