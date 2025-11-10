<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('t_BulkNotifications', static function (Blueprint $table) {
            $table->id('BulkNotificationID');
            $table->string('Label')->nullable();
            $table->string('Module')->index();
            $table->longText('Title')->nullable();
            $table->longText('Content');
            $table->jsonb('Extra')->nullable();
            $table->dateTime('CompleteOn')->nullable();
            $table->unsignedBigInteger('Total');
            $table->foreignId('CreatedBy')->constrained('t_Users', 'Id');
            $table->dateTime('CreatedOn');
            $table->foreignId('ModifiedBy')->constrained('t_Users', 'Id');
            $table->dateTime('ModifiedOn');
            $table->foreignId('DeletedBy')->nullable()->constrained('t_Users', 'Id');
            $table->softDeletes('DeletedOn');
        });

        Schema::table('t_SMS', static function (Blueprint $table) {
            $table->foreignId('BulkNotificationId')->nullable()->after('Status')->constrained('t_BulkNotifications', 'BulkNotificationID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('t_SMS', static function (Blueprint $table) {
            $table->dropConstrainedForeignId('BulkNotificationId');
        });

        Schema::dropIfExists('t_BulkNotifications');
    }
};
